<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Services\Services\PromotionService;
use Modules\Services\Services\PlanService;
use Modules\Services\Http\Requests\PromotionRequest;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    /**
     * @var PromotionService
     */
    protected $promotionService;

    /**
     * @var PlanService
     */
    protected $planService;

    /**
     * PromotionController constructor.
     *
     * @param PromotionService $promotionService
     * @param PlanService $planService
     */
    public function __construct(PromotionService $promotionService, PlanService $planService)
    {
        $this->promotionService = $promotionService;
        $this->planService = $planService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $onlyActive = $request->has('active') && $request->input('active') == 1;

        $result = $this->promotionService->getAllPromotions($onlyActive);

        return view('services::promotions.index', [
            'promotions' => $result['promotions'],
            'onlyActive' => $onlyActive
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $plans = $this->planService->getAllPlans(true)['plans'];

        return view('services::promotions.create', [
            'plans' => $plans,
            'discountTypes' => [
                'percentage' => 'Porcentaje',
                'fixed' => 'Monto fijo'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param PromotionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PromotionRequest $request)
    {
        $data = $request->validated();

        // Si hay planes seleccionados, pasarlos al servicio
        if ($request->has('plan_ids')) {
            $data['plan_ids'] = $request->input('plan_ids');
        }

        $result = $this->promotionService->createPromotion(
            $data,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']])->withInput();
        }

        return redirect()->route('services.promotions.index')
            ->with('success', $result['message']);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $promotion = $this->promotionService->promotionRepository->find($id);
        $plans = $promotion->plans;

        return view('services::promotions.show', [
            'promotion' => $promotion,
            'plans' => $plans
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $promotion = $this->promotionService->promotionRepository->find($id);
        $plans = $this->planService->getAllPlans(true)['plans'];
        $selectedPlans = $promotion->plans->pluck('id')->toArray();

        return view('services::promotions.edit', [
            'promotion' => $promotion,
            'plans' => $plans,
            'selectedPlans' => $selectedPlans,
            'discountTypes' => [
                'percentage' => 'Porcentaje',
                'fixed' => 'Monto fijo'
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param PromotionRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PromotionRequest $request, $id)
    {
        $data = $request->validated();

        // Si hay planes seleccionados, pasarlos al servicio
        if ($request->has('plan_ids')) {
            $data['plan_ids'] = $request->input('plan_ids');
        }

        $result = $this->promotionService->updatePromotion(
            $id,
            $data,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']])->withInput();
        }

        return redirect()->route('services.promotions.index')
            ->with('success', $result['message']);
    }

    /**
     * Activate the specified resource in storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($id, Request $request)
    {
        $result = $this->promotionService->togglePromotionStatus(
            $id,
            true,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Deactivate the specified resource in storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivate($id, Request $request)
    {
        $result = $this->promotionService->togglePromotionStatus(
            $id,
            false,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id, Request $request)
    {
        $result = $this->promotionService->deletePromotion(
            $id,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']]);
        }

        return redirect()->route('services.promotions.index')
            ->with('success', $result['message']);
    }
}
