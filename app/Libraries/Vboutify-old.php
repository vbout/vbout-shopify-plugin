<?php

namespace App\Libraries;

use App\Models\Shop;
use App\Libraries\Vbout\Services\EmailMarketingWS;

class Vboutify
{
    public function start($request)
    {
        $event = $request->header('x-shopify-topic');
        $shopUrl = $request->header('x-shopify-shop-domain');
        $settings = $this->loadSettings($shopUrl);
        $result = 'No settings provided';

        if ($settings && $settings->apiKey) {
            $canUpdate = false;

            switch ($event) {
                case 'customers/create':
                    $listId = $settings->customersList->id;
                    $listFields = isset($settings->customersList->fields) ? $settings->customersList->fields : [];
                    break;
                case 'checkouts/create':
                    if (!$request->gateway) {
                        $listId = $settings->incompletePurchasesList->id;
                        $listFields = isset($settings->incompletePurchasesList->fields) ? $settings->incompletePurchasesList->fields : [];
                    }
                case 'checkouts/update':
                    if (!$request->gateway) {
                        $listId = $settings->incompletePurchasesList->id;
                        $listFields = isset($settings->incompletePurchasesList->fields) ? $settings->incompletePurchasesList->fields : [];
                    } else {
                        // if ($request->customer['accepts_marketing']) {
                        //     $listId = $settings->newsLettersList->id;
                        //     $listFields = isset($settings->newsLettersList->fields) ? $settings->newsLettersList->fields : [];
                        // } else {
                        //     $listId = $settings->completePurchasesList->id;
                        //     $listFields = isset($settings->completePurchasesList->fields) ? $settings->completePurchasesList->fields : [];
                        //     $canUpdate = true;
                        // }
                        $listId = $settings->completePurchasesList->id;
                        $listFields = isset($settings->completePurchasesList->fields) ? $settings->completePurchasesList->fields : [];
                        // $canUpdate = true;
                    }
                    
                    $canUpdate = true;
                    break;
                case 'orders/create':
                    $listId = $settings->completePurchasesList->id;
                    $listFields = isset($settings->completePurchasesList->fields) ? $settings->completePurchasesList->fields : [];
                    $canUpdate = true;
                    break;
                default:
                    $listId = false;
                    $listFields = false;
                    break;
            }

            if (!$listId) {
                $result = 'List ID does not exists';
            } else {
                try {
                    // Get webhook data
                    $data = $request->all();

                    $customerEmail = '';
                    if (isset($data['customer']['email'])) {
                        $customerEmail = $data['customer']['email'];
                    }

                    // $customer = (isset($data['customer'])) ? $data['customer'] : $data;
                    $customer = [];
                    if (isset($data['shipping_address'])) {
                        $customer = $data['shipping_address'];
                    } else {
                        if (isset($data['customer'])) {
                            $customer = $data['customer'];
                        } else {
                            $customer = $data;
                        }
                    }

                    // $address = (isset($customer['shipping_address'])) ? $customer['shipping_address'] : [];

                    $fields = [];
                    if ($listFields) {
                        // Map the fields
                        foreach ($listFields as $field) {
                            if ($field !== '') {
                                list($fieldKey, $fieldVal) = explode('|', $field);
                                $fieldName = preg_replace('/ /', '_', strtolower($fieldVal));

                                if ($fieldName === 'phone_number') {
                                    $fieldName = 'phone';
                                }

                                if ($fieldName === 'state') {
                                    $fieldName = 'province';
                                }

                                // For customer fields
                                if (isset($customer[$fieldName])) {
                                    $fields[$fieldKey] = $customer[$fieldName];
                                }

                                // For address fields
                                // if (isset($address[$fieldName])) {
                                //     // Do not use the name on billing address, use customer name only
                                //     if ($fieldName !== 'first_name' && $fieldName !== 'last_name') {
                                //         $fields[$fieldKey] = $address[$fieldName];
                                //     }
                                // }

                                // For other fields
                                // if (isset($data[$fieldName])) {
                                //     $fields[$fieldKey] = $data[$fieldName];
                                // }
                            }
                        }
                    }

                    // Vbout API for Email Marketing
                    $vboutApp = new EmailMarketingWS(['api_key' => $settings->apiKey]);

                    // Request payload
                    $payload = [
                        'email' => $customerEmail,
                        'status' => 'Active',
                        'listid' => $listId,
                        'fields' => $fields
                    ];

                    if ($canUpdate) {
                        // Check if contact exists in incomplete purchases
                        $contact = $vboutApp->searchContact($customerEmail, $settings->incompletePurchasesList->id);

                        // Add contact to complete purchase
                        if (isset($contact['id']) && $contact['id'] !== '') {
                            $payload['id'] = $contact['id'];
                            $result = $vboutApp->updateContact($payload);
                        } else {
                            $result = $vboutApp->addNewContact($payload);
                        }
                    } else {
                        $result = $vboutApp->addNewContact($payload);
                    }

                    // Additional customer sync when checking out
                    if ($event === 'checkouts/update' && $customerEmail !== '') {
                        // Check if contact exists in customers list
                        $cContact = $vboutApp->searchContact($customerEmail, $settings->customersList->id);

                        $cFields = [];
                        if (isset($settings->customersList->fields)) {
                            $cFields = $this->mapFields($settings->customersList->fields, $customer);
                        }

                        // Add contact to customers list
                        $cPayload = [
                            'email' => $customerEmail,
                            'status' => 'Active',
                            'listid' => $settings->customersList->id,
                            'fields' => $cFields
                        ];

                        if (isset($cContact['id']) && $cContact['id'] !== '') {
                            $cPayload['id'] = $cContact['id'];
                            $result = $vboutApp->updateContact($cPayload);
                        } else {
                            $result = $vboutApp->addNewContact($cPayload);
                        }
                    }

                    // Newsletter subscription
                    if (isset($request->customer['accepts_marketing']) && $event === 'orders/create') {
                        // Check if contact exists in newsletter list
                        $nlContact = $vboutApp->searchContact($customerEmail, $settings->newsLettersList->id);

                        $nlFields = [];
                        if (isset($settings->newsLettersList->fields)) {
                            $nlFields = $this->mapFields($settings->newsLettersList->fields, $customer);
                        }

                        // Add contact to newsletter list
                        $nlPayload = [
                            'email' => $customerEmail,
                            'status' => 'Active',
                            'listid' => $settings->newsLettersList->id,
                            'fields' => $nlFields
                        ];

                        if (isset($nlContact['id']) && $nlContact['id'] !== '') {
                            if (!$request->customer['accepts_marketing']) {
                                $result = $vboutApp->removeContact($nlContact['id']);
                            } else {
                                $nlPayload['id'] = $nlContact['id'];
                                $result = $vboutApp->updateContact($nlPayload);
                            }
                        } else {
                            if ($request->customer['accepts_marketing']) {
                                $result = $vboutApp->addNewContact($nlPayload);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $result = $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine();
                }
            }
        }

        return $result;
    }

    public function loadSettings($shopUrl)
    {
        $shop = Shop::where('shop_url', $shopUrl)->first();
        $settings = ($shop) ? json_decode($shop->settings) : null;

        return $settings;
    }

    public function mapFields($listFields, $customer)
    {
        $fields = [];
        if ($listFields) {
            // Map the fields
            foreach ($listFields as $field) {
                if ($field !== '') {
                    list($fieldKey, $fieldVal) = explode('|', $field);
                    $fieldName = preg_replace('/ /', '_', strtolower($fieldVal));

                    if ($fieldName === 'phone_number') {
                        $fieldName = 'phone';
                    }

                    if ($fieldName === 'state') {
                        $fieldName = 'province';
                    }

                    // For customer fields
                    if (isset($customer[$fieldName])) {
                        $fields[$fieldKey] = $customer[$fieldName];
                    }

                    // For address fields
                    // if (isset($address[$fieldName])) {
                    //     // Do not use the name on billing address, use customer name only
                    //     if ($fieldName !== 'first_name' && $fieldName !== 'last_name') {
                    //         $fields[$fieldKey] = $address[$fieldName];
                    //     }
                    // }

                    // For other fields
                    // if (isset($data[$fieldName])) {
                    //     $fields[$fieldKey] = $data[$fieldName];
                    // }
                }
            }
        }

        return $fields;
    }
}