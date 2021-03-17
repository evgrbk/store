<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqRequest;
use App\Http\Requests\Faq\IndexFaqRequest;
use App\Http\Requests\Faq\UpdateFaqRequest;
use App\Http\Resources\Faq\FaqResource;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    /**
     * @var FaqService
     */
    protected $faq_service;

    /**
     * FaqController constructor.
     *
     * @param FaqService $service
     */
    public function __construct(FaqService $service)
    {
        $this->faq_service = $service;
    }

    /**
     * Get all questions and answers.
     *
     * @param IndexFaqRequest $request
     * @return JsonResponse
     */
    public function index(IndexFaqRequest $request): JsonResponse
    {
        $limit = $request->get('limit');

        $allFaq = $this->faq_service
            ->getAllFaq($limit);

        return response()->json([
            'data' => FaqResource::collection($allFaq),
            'count' => $allFaq->total(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Create new question.
     *
     * @param FaqRequest $request
     * @return JsonResponse
     */
    public function store(FaqRequest $request): JsonResponse
    {
        $this->checkPermission('create-faq');

        $newFaq = $this->faq_service
            ->createNewFaq($request->all());

        return response()->json([
            'data' => new FaqResource($newFaq),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->faq_service
            ->getFaq($id);

        return response()
            ->json( new FaqResource($data)
            , JsonResponse::HTTP_OK);
    }

    /**
     * Update one faq record
     *
     * @param UpdateFaqRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateFaqRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-faq');

        $data = $this->faq_service
            ->updateFaq($id, $request->validated());

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * Delete faq.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-faq');

        $data = $this->faq_service
            ->destroyOneFaq($id);

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * @param IndexFaqRequest $request
     * @return JsonResponse
     */
    public function getFaq(IndexFaqRequest $request): JsonResponse
    {
        $limit = $request->get('limit');

        $allFaq = $this->faq_service
            ->getAllFaq($limit);

        return response()->json([
            'data' => FaqResource::collection($allFaq),
            'count' => $allFaq->total(),
        ], JsonResponse::HTTP_OK);
    }
}
