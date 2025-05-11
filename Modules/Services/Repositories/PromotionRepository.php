<?php

namespace Modules\Services\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Services\Entities\Promotion;

class PromotionRepository extends BaseRepository
{
    /**
     * PromotionRepository constructor.
     *
     * @param Promotion $model
     */
    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }

    /**
     * Obtener promociones activas y vigentes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrentPromotions()
    {
        return $this->model->currentlyActive()->get();
    }

    /**
     * Obtener promociones aplicables a un plan.
     *
     * @param int $planId
     * @param bool $onlyActive
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPromotionsForPlan($planId, $onlyActive = true)
    {
        $query = $this->model->whereHas('plans', function($query) use ($planId) {
            $query->where('plans.id', $planId);
        });

        if ($onlyActive) {
            $query->currentlyActive();
        }

        return $query->get();
    }

    /**
     * Verificar si un plan tiene alguna promoción activa.
     *
     * @param int $planId
     * @return bool
     */
    public function planHasActivePromotion($planId)
    {
        return $this->model->currentlyActive()
            ->whereHas('plans', function($query) use ($planId) {
                $query->where('plans.id', $planId);
            })
            ->exists();
    }
}
