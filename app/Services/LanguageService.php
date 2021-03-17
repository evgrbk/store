<?php

namespace App\Services;

use App\Repositories\LanguageRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LanguageService extends Service
{
    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    /**
     * LanguageService constructor.
     *
     * @param LanguageRepository $languageRepository
     */
    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * Get list of languages
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        return $this->languageRepository
            ->paginateAll($limit);
    }

    /**
     * Create language
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        //Check primary if not exists
        $primary = $this->languageRepository->getWhere('primary', '1')->first();
        if (!$primary) {
            $data['primary'] = 1;
        } else {
            if ($data['primary']) {
                //Set current primary to false
                $this->languageRepository
                    ->update(['primary' => false], $primary->id);
            }
        }

        return $this->languageRepository
            ->store($data);
    }

    /**
     * Update language
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        $primary = $this->languageRepository->getWhere('primary', '1')->first();

        if ($primary) {
            if ($primary->id === $id) {
                //Dont change primary if already true
                $data['primary'] = 1;
            } else if ($data['primary']) {
                //Set current primary to false
                $this->languageRepository
                    ->update(['primary' => false], $primary->id);
            }
        }

        return $this->languageRepository
            ->update($data, $id);
    }

    /**
     * Delete language
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($this->languageRepository->countAll() == 1) {
            return false;
        }

        $primary = $this->languageRepository->getWhere('primary', '1')->first();

        $result = $this->languageRepository
            ->destroy($id);

        if ($primary && $primary->id == $id) {
            $this->languageRepository
                ->update(['primary' => true], $this->languageRepository->all()->first()->id);
        }

        return $result;
    }

    /**
     * Get all names
     *
     * @return Collection
     */
    public function getNames(): Collection
    {
        return $this->languageRepository
            ->allColumns(['id', 'name']);
    }
}
