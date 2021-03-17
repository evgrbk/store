<?php

namespace App\Services\GoodsImport;


use App\Http\Requests\AdminPanel\Good\PrepareImportGoodRequest;
use App\Http\Requests\AdminPanel\Good\ImportGoodRequest;
use App\Services\Service;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ParserService extends Service
{
    const STORAGE_PATH = 'goods_import';

    /**
     * Prepare goods file to import
     *
     * @param PrepareImportGoodRequest $request
     * @param string $fileContent
     * @return JsonResponse
     */
    public function prepare(PrepareImportGoodRequest $request, $fileContent = ''): JsonResponse
    {
        //Upload file
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $fileName = $this->storeFile($request->file);
        } else if (!$fileContent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Файл для импорта отсутствует!',
            ], 422);
        }

        //Get xml from request
        if ($fileContent) {
            try {
                $xml = simplexml_load_string($fileContent);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Данные в импортируемом файле не поддерживаются!',
                ], 422);
            }
        } else {
            $xml = simplexml_load_file($request->file);
        }

        //Get parsers in vendor folder
        $namespace = __NAMESPACE__;
        $filesList = glob(base_path() . '/app/Services/GoodsImport/Vendors/*.php');

        //Search parser
        foreach ($filesList as $parserFileName) {
            $parserClassName = basename($parserFileName, ".php");

            try {
                $classPath = $namespace . '\\Vendors\\' . $parserClassName;
                if (app($classPath)->detect($xml)) {
                    $src = app($classPath)->getSrc($xml);

                    if (empty($src)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'В импортируемом файле товары не найдены!',
                        ], 422);
                    }

                    $responseArray = [
                        'status' => 'ok',
                        'type' => $parserClassName,
                        'fields' => [
                            'src' => $src,
                            'dest' => app($classPath)->getDest()
                        ]
                    ];

                    if (!$fileContent) {
                        $responseArray['file'] = $fileName;
                    }

                    return response()->json($responseArray);
                }
            } catch (Exception $e) {
                report($e);
            }
        }

        //If parser not found
        if (!$fileContent)
            $this->deleteFile($fileName);

        return response()->json([
            'status' => 'error',
            'message' => 'Данные в импортируемом файле не поддерживаются!',
        ], 422);

    }

    /**
     * Import check
     *
     * @param ImportGoodRequest $request
     * @param string $fileContent
     * @return JsonResponse
     */
    public function import(ImportGoodRequest $request, $fileContent = ''): JsonResponse
    {
        //Check file exists
        if (!$fileContent && !Storage::exists(self::STORAGE_PATH . '/' . basename($request->file))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Импортируемый файл не найден!',
            ], 422);
        }

        //Check parser exists
        $namespace = __NAMESPACE__;
        $classPath = $namespace . '\\Vendors\\' . $request->type;

        if (!class_exists($classPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Тип обработчика импортируемого файла не поддерживается!',
            ], 422);
        }

        //Get file
        if ($fileContent) {
            try {
                $xml = simplexml_load_string($fileContent);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Данные в импортируемом файле не поддерживаются!',
                ], 422);
            }
        } else {
            $xml = simplexml_load_string($this->getFile($request->file));
        }

        //Compare field with src from parser
        $requestFields = $request->fields;
        $src = app($classPath)->getSrc($xml);

        foreach ($requestFields as $key => $field) {
            if (!in_array($key, $src)) {
                unset($requestFields[$key]);
            }
        }

        //Check fields not null
        if (!count($requestFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Не указаны данные для импорта!',
            ], 422);
        }

        return app($classPath)->importDB($xml, $requestFields);
    }

    /**
     * Store import file
     *
     * @param object $file
     * @return string
     */
    public function storeFile(object $file): string
    {
        $path = basename($file->store(self::STORAGE_PATH));

        return $path;
    }

    /**
     * Get file by name
     *
     * @param string $fileName
     * @return string
     */
    public function getFile(string $fileName): string
    {
        return Storage::get(self::STORAGE_PATH . '/' . basename($fileName));
    }

    /**
     * Delete file
     *
     * @param string $fileName
     * @return string
     */
    public function deleteFile(string $fileName): string
    {
        return Storage::delete(self::STORAGE_PATH . '/' . basename($fileName));
    }
}
