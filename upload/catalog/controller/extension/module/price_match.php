<?php
class ControllerExtensionModulePriceMatch extends Controller {

    // Render the price match form (AJAX)
    public function index() {
        $this->load->language('extension/module/price_match');

        $data['heading_title']          = $this->language->get('heading_title');
        $data['text_description']       = $this->language->get('text_description');
        $data['entry_firstname']        = $this->language->get('entry_firstname');
        $data['entry_lastname']         = $this->language->get('entry_lastname');
        $data['entry_email']            = $this->language->get('entry_email');
        $data['entry_telephone']        = $this->language->get('entry_telephone');
        $data['entry_competitor_name']  = $this->language->get('entry_competitor_name');
        $data['entry_competitor_url']   = $this->language->get('entry_competitor_url');
        $data['entry_competitor_price'] = $this->language->get('entry_competitor_price');
        $data['entry_comment']          = $this->language->get('entry_comment');
        $data['button_submit']          = $this->language->get('button_submit');

        $product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
        $data['product_id'] = $product_id;

        $data['send_url'] = $this->url->link('extension/module/price_match/send', '', true);

        // Pre-fill fields if customer is logged in
        if ($this->customer->isLogged()) {
            $data['firstname']  = $this->customer->getFirstName();
            $data['lastname']   = $this->customer->getLastName();
            $data['email']      = $this->customer->getEmail();
            $data['telephone']  = $this->customer->getTelephone();
        } else {
            $data['firstname']  = '';
            $data['lastname']   = '';
            $data['email']      = '';
            $data['telephone']  = '';
        }

        $json = array();
        $json['html'] = $this->load->view('extension/module/price_match', $data);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Process the submitted form
    public function send() {
        $this->load->language('extension/module/price_match');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $post = $this->request->post;

            // Validation
            if (empty($post['firstname']) || utf8_strlen($post['firstname']) < 1 || utf8_strlen($post['firstname']) > 32) {
                $json['error']['firstname'] = $this->language->get('error_firstname');
            }

            if (empty($post['lastname']) || utf8_strlen($post['lastname']) < 1 || utf8_strlen($post['lastname']) > 32) {
                $json['error']['lastname'] = $this->language->get('error_lastname');
            }

            if (empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
                $json['error']['email'] = $this->language->get('error_email');
            }

            if (empty($post['competitor_name'])) {
                $json['error']['competitor_name'] = $this->language->get('error_competitor_name');
            }

            if (empty($post['competitor_url'])) {
                $json['error']['competitor_url'] = $this->language->get('error_competitor_url');
            }

            if (!isset($post['competitor_price']) || !is_numeric($post['competitor_price']) || (float)$post['competitor_price'] <= 0) {
                $json['error']['competitor_price'] = $this->language->get('error_competitor_price');
            }

            if (!$json) {
                $this->load->model('extension/module/price_match');

                $customer_id = $this->customer->isLogged() ? $this->customer->getId() : 0;
                $status_default = (int)$this->config->get('module_price_match_status_default');

                $request_id = $this->model_extension_module_price_match->addRequest(array(
                    'product_id'       => isset($post['product_id']) ? (int)$post['product_id'] : 0,
                    'customer_id'      => $customer_id,
                    'firstname'        => $post['firstname'],
                    'lastname'         => $post['lastname'],
                    'email'            => $post['email'],
                    'telephone'        => isset($post['telephone']) ? $post['telephone'] : '',
                    'competitor_name'  => $post['competitor_name'],
                    'competitor_url'   => $post['competitor_url'],
                    'competitor_price' => (float)$post['competitor_price'],
                    'comment'          => isset($post['comment']) ? $post['comment'] : '',
                    'status'           => $status_default,
                ));

                // Send notification email if enabled
                if ($this->config->get('module_price_match_notification')) {
                    $admin_email = $this->config->get('module_price_match_admin_email');
                    if (!$admin_email) {
                        $admin_email = $this->config->get('config_email');
                    }

                    if ($admin_email) {
                        $this->load->model('catalog/product');
                        $product_info = $this->model_catalog_product->getProduct((int)$post['product_id']);
                        $product_name = $product_info ? $product_info['name'] : 'Product #' . (int)$post['product_id'];

                        $subject = sprintf('[%s] New Price Match Request - %s', $this->config->get('config_name'), $product_name);

                        $message  = "A new price match request has been submitted.\n\n";
                        $message .= "Product: " . $product_name . "\n";
                        $message .= "Customer: " . $post['firstname'] . " " . $post['lastname'] . "\n";
                        $message .= "Email: " . $post['email'] . "\n";
                        if (!empty($post['telephone'])) {
                            $message .= "Telephone: " . $post['telephone'] . "\n";
                        }
                        $message .= "\nCompetitor Store: " . $post['competitor_name'] . "\n";
                        $message .= "Competitor URL: " . $post['competitor_url'] . "\n";
                        $message .= "Competitor Price: " . number_format((float)$post['competitor_price'], 2) . "\n";
                        if (!empty($post['comment'])) {
                            $message .= "\nAdditional Comments:\n" . $post['comment'] . "\n";
                        }
                        $message .= "\nRequest ID: #" . $request_id . "\n";

                        $mail = new Mail($this->config->get('config_mail_engine'));
                        $mail->parameter = $this->config->get('config_mail_parameter');
                        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                        $mail->smtp_port     = $this->config->get('config_mail_smtp_port');
                        $mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');
                        $mail->setTo($admin_email);
                        $mail->setFrom($this->config->get('config_email'));
                        $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                        $mail->setSubject($subject);
                        $mail->setText($message);
                        $mail->send();
                    }
                }

                $json['success'] = $this->language->get('text_success');
            }
        } else {
            $json['error']['warning'] = 'Invalid request method.';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
