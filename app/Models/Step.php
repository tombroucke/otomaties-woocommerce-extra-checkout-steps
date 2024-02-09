<?php

namespace Otomaties\WooCommerceExtraCheckoutSteps\Models;

class Step
{
    public function __construct(
        private int $id,
        private string $title,
    ) {
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getUrl() : string
    {
        return get_permalink($this->id);
    }

    public function addNotice(string $message, string $type = 'error') : Step
    {
        $notices = $this->getSessionData('notices') ?? [];
        $notices[] = [
            'message' => $message,
            'type' => $type,
        ];
        $this->setSessionData('notices', $notices);
        return $this;
    }

    public function notices() : array
    {
        return $this->getSessionData('notices') ?? [];
    }

    public function clearNotices() : Step
    {
        $this->setSessionData('notices', []);
        return $this;
    }

    public function setData($data) : Step
    {
        $this->setSessionData('data', $data);
        return $this;
    }

    public function getData() : array
    {
        return $this->getSessionData('data') ?? [];
    }

    public function complete() : Step
    {
        $this->setSessionData('completed', true);
        return $this;
    }

    public function incomplete() : Step
    {
        $this->setSessionData('completed', false);
        return $this;
    }

    public function isCompleted() : bool
    {
        return $this->getSessionData('completed') === true;
    }

    public function verifyNonce($nonce = null) : Step
    {
        if (!$nonce) {
            $nonce = sanitize_text_field(filter_input(INPUT_POST, 'extra-checkout-field' . $this->getId() . '-nonce'));
        }

        if (!wp_verify_nonce($nonce, 'extra-checkout-field' . $this->getId())) {
            $this->addNotice(__('Error verifying your request. Please try again', 'woocommerce-extra-checkout-steps'));
        }
        return $this;
    }

    public function verifyFields(array $fields) : Step
    {
        collect($fields)
            ->map(function ($field, $key) {
                $validationFunctions = $field['validate'] ?? [];
                foreach ($validationFunctions as $function) {
                    if (is_array($function) && isset($function['function']) && is_callable($function['function']) && !$function['function']($_POST[$key])) {
                        $this->addNotice($function['message'] ?? sprintf(__('Error validating field %s', 'woocommerce-extra-checkout-steps'), '<strong>' . $field['label'] . '</strong>'));
                    }
                    if ('required' === $function && empty($_POST[$key])) {
                        $this->addNotice(
                            sprintf(
                                __('Field %s is required', 'woocommerce-extra-checkout-steps'), 
                                '<strong>' . $field['label'] . '</strong>'
                            )
                        );
                    }
                }
            });

        return $this;
    }

    private function setSessionData($key, $value) : Step
    {
        $completedSteps = WC()->session->get('completed_extra_checkout_steps');
        if (!isset($completedSteps[$this->getId()])) {
            $completedSteps[$this->getId()] = [];
        }
        $completedSteps[$this->getId()][$key] = $value;
        WC()->session->set('completed_extra_checkout_steps', $completedSteps);
        return $this;
    }

    private function getSessionData($key)
    {
        $stepId = $this->getId();
        $completedSteps = WC()->session->get('completed_extra_checkout_steps');
        if (!isset($completedSteps[$stepId]) || !isset($completedSteps[$stepId][$key])) {
            return null;
        }
        return $completedSteps[$this->getId()][$key];
    }

}
