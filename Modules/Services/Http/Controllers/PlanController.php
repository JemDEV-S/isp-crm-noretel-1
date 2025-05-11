<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Services\Services\PlanService;
use Modules\Services\Services\ServiceService;
use Modules\Services\Services\PromotionService;
use Modules\Services\Http\Requests\PlanRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Services\Repositories\PlanRepository;

class PlanController extends Controller
{
    /**
     * @var PlanRepository
     */
    protected $planRepository;

    /**
     * @var PlanService
     */
    protected $planService;

    /**
     * @var ServiceService
     */
    protected $serviceService;

    /**
     * @var PromotionService
     */
    protected $promotionService;

    /**
     * PlanController constructor.
     *
     * @param PlanService $planService
     * @param ServiceService $serviceService
     * @param PromotionService $promotionService
     */
    public function __construct(
        PlanRepository $planRepository,
        PlanService $planService,
        ServiceService $serviceService,
        PromotionService $promotionService
    ) {
        $this->planRepository = $planRepository;
        $this->planService = $planService;
        $this->serviceService = $serviceService;
        $this->promotionService = $promotionService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $serviceId = $request->input('service_id');

        if ($serviceId) {
            $result = $this->planService->getPlansByService($serviceId);
            $service = $result['service'] ?? null;
            $plans = $result['plans'] ?? [];
        } else {
            $result = $this->planService->getAllPlans();
            $service = null;
            $plans = $result['plans'];
        }

        return view('services::plans.index', [
            'plans' => $plans,
            'service' => $service
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $serviceId = $request->input('service_id');

        $services = $this->serviceService->getAllServices(true)['services'];
        $promotions = $this->promotionService->getAllPromotions(true)['promotions'];

        $selectedService = null;
        if ($serviceId) {
            foreach ($services as $service) {
                if ($service->id == $serviceId) {
                    $selectedService = $service;
                    break;
                }
            }
        }

        return view('services::plans.create', [
            'services' => $services,
            'promotions' => $promotions,
            'selectedService' => $selectedService
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param PlanRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PlanRequest $request)
    {
        $result = $this->planService->createPlan(
            $request->validated(),
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']])->withInput();
        }

        if ($request->input('service_id')) {
            return redirect()->route('services.plans.index', ['service_id' => $request->input('service_id')])
                ->with('success', $result['message']);
        }

        return redirect()->route('services.plans.index')
            ->with('success', $result['message']);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $plan = $this->planRepository->find($id);
        $service = $plan->service;
        $activePromotions = $plan->promotions()->currentlyActive()->get();
        $inactivePromotions = $plan->promotions()->where('active', true)
            ->where(function($query) {
                $query->where('start_date', '>', now())
                    ->orWhere('end_date', '<', now());
            })->get();

        return view('services::plans.show', [
            'plan' => $plan,
            'service' => $service,
            'activePromotions' => $activePromotions,
            'inactivePromotions' => $inactivePromotions
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $plan = $this->planRepository->find($id);
        $services = $this->serviceService->getAllServices(true)['services'];
        $promotions = $this->promotionService->getAllPromotions()['promotions'];

        $selectedPromotions = $plan->promotions->pluck('id')->toArray();

        return view('services::plans.edit', [
            'plan' => $plan,
            'services' => $services,
            'promotions' => $promotions,
            'selectedPromotions' => $selectedPromotions
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param PlanRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PlanRequest $request, $id)
    {
        $result = $this->planService->updatePlan(
            $id,
            $request->validated(),
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']])->withInput();
        }

        if ($request->input('service_id')) {
            return redirect()->route('services.plans.index', ['service_id' => $request->input('service_id')])
                ->with('success', $result['message']);
        }

        return redirect()->route('services.plans.index')
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
        $result = $this->planService->togglePlanStatus(
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
        $result = $this->planService->togglePlanStatus(
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
        $plan = $this->planRepository->find($id);
        $serviceId = $plan->service_id;

        $result = $this->planService->deletePlan(
            $id,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']]);
        }

        if ($request->query('service_id')) {
            return redirect()->route('services.plans.index', ['service_id' => $serviceId])
                ->with('success', $result['message']);
        }

        return redirect()->route('services.plans.index')
            ->with('success', $result['message']);
    }
}
