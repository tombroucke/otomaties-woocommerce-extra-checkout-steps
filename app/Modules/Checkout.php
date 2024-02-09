<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\Modules;

use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Steps;
use Otomaties\WooCommerceExtraCheckoutSteps\Modules\Abstracts\Module;
use Illuminate\Support\Collection;

class Checkout extends Module
{
    public function init()
    {
        $this->loader->addFilter('woocommerce_get_checkout_order_received_url', $this, 'customOrderReceivedUrl', 10, 2);
        $this->loader->addFilter('woocommerce_get_checkout_url', $this, 'customCheckoutUrl');
        $this->loader->addAction('template_redirect', $this, 'addNotices');
        $this->loader->addAction('template_redirect', $this, 'maybeRedirectStep');
        $this->loader->addAction('template_redirect', $this, 'prePopulateFields', 10);
        $this->loader->addAction('woocommerce_cart_emptied', $this, 'clearSessionData', 10);
        $this->loader->addAction('admin_post_extra_checkout_fields_save_data', $this, 'saveCheckoutStepData');
        $this->loader->addAction('admin_post_nopriv_extra_checkout_fields_save_data', $this, 'saveCheckoutStepData');
    }

    public function customCheckoutUrl($url)
    {
        return get_permalink($this->plugin->make(Steps::class)->get()->first()->getId());
    }

    public function prePopulateFields()
    {
        $requestedStep = $this->requestedStep();

        // Get all default checkout fields
        $checkoutFields = Collection::make(WC()->checkout->get_checkout_fields())
            ->flatMap(function ($fields) {
                return array_keys($fields);
            })
            ->push('createaccount')
            ->all();
        

        // Get all data from all steps
        $allStepsData = $this->plugin->make(Steps::class)
            ->get()
            ->mapWithKeys(function ($step) {
                return [$step->getId() => $step->getData()];
            })
            ->flatMap(function ($data) {
                return $data;
            })
            ->toArray();

        add_filter('woocommerce_checkout_get_value', function ($value, $input) use ($requestedStep, $checkoutFields, $allStepsData) {
            // Get value from current step
            if ($requestedStep) {
                $data = $requestedStep->getData();
                if (isset($data[$input])) {
                    return $data[$input];
                }
            }
            // Get value from all steps if it's a default checkout field
            if (in_array($input, $checkoutFields) && isset($allStepsData[$input])) {
                if ($input === 'createaccount') {
                    return $allStepsData[$input] === '1' ? true : false;
                }
                return $allStepsData[$input];
            }
            return $value;
        }, 10, 2);
    }

    public function addNotices()
    {
        $requestedStep = $this->requestedStep();
        if (!$requestedStep) {
            return;
        }
        
        foreach ($requestedStep->notices() as $notice) {
            wc_add_notice($notice['message'], $notice['type']);
            $requestedStep->clearNotices();
        }
    }

    public function customOrderReceivedUrl($orderReceivedUrl, $order)
    {
        $step = $this->plugin->make(Steps::class)->get()->last();
        if ($step) {
            $url = $step->getUrl();
            $orderReceivedUrl = wc_get_endpoint_url('order-received', $order->get_id(), $url);
            $orderReceivedUrl = add_query_arg('key', $order->get_order_key(), $orderReceivedUrl);
        }
        return $orderReceivedUrl;
    }

    public function maybeRedirectStep()
    {
        $requestedStep = $this->requestedStep();

        if (!$requestedStep) {
            return;
        }
        
        $cartIsEmpty = WC()->cart->is_empty();
        if ($cartIsEmpty) {
            wp_redirect(get_permalink(wc_get_page_id('cart')));
            exit;
        }

        $previousSteps = $this->plugin->make(Steps::class)->previousSteps($requestedStep);
        foreach ($previousSteps as $step) {
            if (!$step->isCompleted()) {
                wp_safe_redirect($step->getUrl());
                exit;
            }
        }
    }

    private function requestedStep()
    {
        global $wp;

        if (!is_page()) {
            return;
        }

        if (!empty($wp->query_vars['order-pay']) || !empty($wp->query_vars['order-received'])) {
            return;
        }
        $steps = $this->plugin->make(Steps::class);
        $requestedStep = $steps->find(get_the_ID());

        if (!$requestedStep) {
            return null;
        }

        return $requestedStep;
    }

    public function clearSessionData()
    {
        $this->plugin->make(Steps::class)->clearSessionData();
    }

    public function saveCheckoutStepData()
    {
        $callback = sanitize_text_field($_POST['extra_checkout_step_name']);
        $stepId = filter_input(INPUT_POST, 'extra_checkout_step_step_id', FILTER_SANITIZE_NUMBER_INT);
        $data = collect($_POST)
            ->reject(function ($value, $key) {
                return in_array($key, ['action']) || strpos($key, 'nonce') !== false || strpos($key, 'http_referer') !== false || strpos($key, 'extra_checkout_step') !== false;
            })
            ->map(function ($value, $key) {
                return sanitize_text_field($value);
            })
            ->toArray();
        $steps = WooCommerceExtraCheckoutSteps()->make(Steps::class);

        $currentStep = $steps->find($stepId);
        $currentStep->setData($data);
        $currentStep->verifyNonce();

        $nextStep = $steps->next($currentStep);

        do_action('woocommerce_extra_checkout_steps_verify_fields', $callback, $currentStep, $nextStep, $steps);

        if (count($currentStep->notices()) > 0) {
            $currentStep->incomplete();
            wp_safe_redirect($currentStep->getUrl());
            exit;
        }

        $data = apply_filters('woocommerce_extra_checkout_steps_save_data', $data, $callback, $currentStep, $nextStep);

        $currentStep->setData($data);
        $currentStep->complete();

        do_action('woocommerce_extra_checkout_steps_data_saved', $callback, $data, $currentStep, $nextStep);

        wp_safe_redirect($nextStep->getUrl());
        exit;
    }
}
