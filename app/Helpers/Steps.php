<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\Helpers;

use Illuminate\Support\Collection;
use Otomaties\WooCommerceExtraCheckoutSteps\Models\Step;

class Steps
{
    private Collection $steps;

    // private int $currentStep = 0;

    public function __construct()
    {
        $this->steps = collect();
        $stepCount = get_option('options_extra_checkout_steps');
        for ($i = 0; $i < $stepCount; $i++) {
            $this->steps->push((int)get_option('options_extra_checkout_steps_' . $i . '_page'));
        }

        $checkoutPageId = (int)get_option('woocommerce_checkout_page_id');
        if (!$this->steps->contains($checkoutPageId)) {
            $this->steps->push($checkoutPageId);
        }

        WC()->initialize_session();
        if (!WC()->session->__isset('completed_extra_checkout_steps')) {
            WC()->session->set('completed_extra_checkout_steps', []);
        }
    }

    public function firstIncompleteStep() : ?Step
    {
        return $this
            ->get()
            ->filter(function ($step) {
                return !$step->isCompleted();
            })
            ->first();
    }

    public function next(Step $step) : ?Step
    {
        return $this->nextSteps($step)->first();
    }

    public function nextSteps(Step $step) : Collection
    {
        return $this->get()->slice($this->stepIndex($step) + 1);
    }

    public function previous(Step $step) : ?Step
    {
        return $this->previousSteps($step)->last();
    }

    public function previousSteps(Step $step) : Collection
    {
        return $this->get()->slice(0, $this->stepIndex($step));
    }

    private function stepIndex(Step $step) : int
    {
        return $this->get()->search(function ($s) use ($step) {
            return $s->getId() === $step->getId();
        });
    }

    public function find(int $postId) : ?Step
    {
        return $this
            ->get()
            ->filter(function ($step) use ($postId) {
                return $step->getId() === $postId;
            })
            ->first();
    }

    public function get() : Collection
    {
        return $this
            ->steps
            ->map(function ($step) {
                return new Step($step, get_the_title($step));
            });
    }
    
    public function clearSessionData() : void
    {
        WC()->session->__unset('completed_extra_checkout_steps');
    }
}
