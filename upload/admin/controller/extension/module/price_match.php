<?php
class ControllerExtensionModulePriceMatch extends Controller {

    private $error = array();

    // -------------------------------------------------------------------------
    // Module Settings
    // -------------------------------------------------------------------------
    public function index() {
        $this->load->language('extension/module/price_match');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_price_match', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/price_match', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['action']  = $this->url->link('extension/module/price_match', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel']  = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['requests_url'] = $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        // Settings
        $fields = array('module_price_match_status', 'module_price_match_notification', 'module_price_match_admin_email', 'module_price_match_status_default');
        foreach ($fields as $field) {
            $data[$field] = isset($this->request->post[$field])
                ? $this->request->post[$field]
                : $this->config->get($field);
        }

        // Language strings for the template
        $lang_keys = array(
            'heading_title', 'text_edit', 'text_enabled', 'text_disabled', 'text_yes', 'text_no',
            'text_pending', 'text_approved', 'text_list',
            'tab_general', 'entry_status', 'entry_notification', 'entry_admin_email', 'entry_status_default',
            'button_save', 'button_cancel',
        );
        foreach ($lang_keys as $key) {
            $data[$key] = $this->language->get($key);
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/price_match', $data));
    }

    // -------------------------------------------------------------------------
    // Requests List
    // -------------------------------------------------------------------------
    public function requests() {
        $this->load->language('extension/module/price_match');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/price_match');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/price_match', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_list'),
            'href' => $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'], true),
        );

        // Filters
        $filter_product = isset($this->request->get['filter_product']) ? $this->request->get['filter_product'] : '';
        $filter_email   = isset($this->request->get['filter_email'])   ? $this->request->get['filter_email']   : '';
        $filter_status  = isset($this->request->get['filter_status'])  ? $this->request->get['filter_status']  : '';

        $sort  = isset($this->request->get['sort'])  ? $this->request->get['sort']  : 'pm.date_added';
        $order = isset($this->request->get['order']) ? $this->request->get['order'] : 'DESC';
        $page  = isset($this->request->get['page'])  ? (int)$this->request->get['page'] : 1;
        $limit = 20;

        $filter_data = array(
            'filter_product' => $filter_product,
            'filter_email'   => $filter_email,
            'filter_status'  => $filter_status,
            'sort'           => $sort,
            'order'          => $order,
            'start'          => ($page - 1) * $limit,
            'limit'          => $limit,
        );

        $results     = $this->model_extension_module_price_match->getRequests($filter_data);
        $total       = $this->model_extension_module_price_match->getTotalRequests($filter_data);

        $data['requests'] = array();

        foreach ($results as $result) {
            $status_text = $this->language->get('text_pending');
            if ($result['status'] == 1) {
                $status_text = $this->language->get('text_approved');
            } elseif ($result['status'] == 2) {
                $status_text = $this->language->get('text_rejected');
            }

            $data['requests'][] = array(
                'price_match_id'  => $result['price_match_id'],
                'product_name'    => $result['product_name'] ? $result['product_name'] : $this->language->get('text_no_results'),
                'firstname'       => $result['firstname'],
                'lastname'        => $result['lastname'],
                'email'           => $result['email'],
                'competitor_name' => $result['competitor_name'],
                'competitor_price'=> $result['competitor_price'],
                'status'          => (int)$result['status'],
                'status_text'     => $status_text,
                'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'view'            => $this->url->link('extension/module/price_match/view', 'user_token=' . $this->session->data['user_token'] . '&price_match_id=' . $result['price_match_id'], true),
            );
        }

        // Sorting URLs
        $url = '';
        if ($filter_product) $url .= '&filter_product=' . urlencode($filter_product);
        if ($filter_email)   $url .= '&filter_email='   . urlencode($filter_email);
        if ($filter_status !== '') $url .= '&filter_status=' . $filter_status;

        $data['sort_price_match_id']  = $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'] . '&sort=pm.price_match_id&order=' . ($sort == 'pm.price_match_id' && $order == 'ASC' ? 'DESC' : 'ASC') . $url, true);
        $data['sort_product']         = $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name&order='           . ($sort == 'pd.name'           && $order == 'ASC' ? 'DESC' : 'ASC') . $url, true);
        $data['sort_email']           = $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'] . '&sort=pm.email&order='           . ($sort == 'pm.email'           && $order == 'ASC' ? 'DESC' : 'ASC') . $url, true);
        $data['sort_competitor_price']= $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'] . '&sort=pm.competitor_price&order='. ($sort == 'pm.competitor_price' && $order == 'ASC' ? 'DESC' : 'ASC') . $url, true);
        $data['sort_date_added']      = $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'] . '&sort=pm.date_added&order='     . ($sort == 'pm.date_added'      && $order == 'ASC' ? 'DESC' : 'ASC') . $url, true);

        $data['sort']  = $sort;
        $data['order'] = $order;

        // Pagination
        $pagination        = new Pagination();
        $pagination->total = $total;
        $pagination->page  = $page;
        $pagination->limit = $limit;
        $pagination->url   = $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results']    = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $limit) + 1 : 0, min($page * $limit, $total), $total, ceil($total / $limit));

        $data['filter_product'] = $filter_product;
        $data['filter_email']   = $filter_email;
        $data['filter_status']  = $filter_status;
        $data['user_token']     = $this->session->data['user_token'];
        $data['text_no_results']  = $this->language->get('text_no_results');
        $data['text_confirm']     = $this->language->get('text_confirm');
        $data['text_all_statuses']= $this->language->get('text_all_statuses');
        $data['text_pending']     = $this->language->get('text_pending');
        $data['text_approved']    = $this->language->get('text_approved');
        $data['text_rejected']    = $this->language->get('text_rejected');
        $data['text_view']        = $this->language->get('text_view');
        $data['error_no_selection'] = $this->language->get('error_no_selection');

        $data['delete'] = $this->url->link('extension/module/price_match/delete', 'user_token=' . $this->session->data['user_token'], true);

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/price_match_list', $data));
    }

    // -------------------------------------------------------------------------
    // View / Edit Single Request
    // -------------------------------------------------------------------------
    public function view() {
        $this->load->language('extension/module/price_match');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/price_match');

        $price_match_id = isset($this->request->get['price_match_id']) ? (int)$this->request->get['price_match_id'] : 0;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->validate()) {
                $this->error['warning'] = $this->language->get('error_permission');
            } else {
                $this->model_extension_module_price_match->editRequest($price_match_id, $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_update');
                $this->response->redirect($this->url->link('extension/module/price_match/view', 'user_token=' . $this->session->data['user_token'] . '&price_match_id=' . $price_match_id, true));
            }
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/price_match', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_list'),
            'href' => $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_view'),
            'href' => $this->url->link('extension/module/price_match/view', 'user_token=' . $this->session->data['user_token'] . '&price_match_id=' . $price_match_id, true),
        );

        $request_info = $this->model_extension_module_price_match->getRequest($price_match_id);

        if (!$request_info) {
            $this->response->redirect($this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['price_match_id']   = $request_info['price_match_id'];
        $data['product_name']     = $request_info['product_name'];
        $data['product_id']       = $request_info['product_id'];
        $data['firstname']        = $request_info['firstname'];
        $data['lastname']         = $request_info['lastname'];
        $data['email']            = $request_info['email'];
        $data['telephone']        = $request_info['telephone'];
        $data['competitor_name']  = $request_info['competitor_name'];
        $data['competitor_url']   = $request_info['competitor_url'];
        $data['competitor_price'] = $request_info['competitor_price'];
        $data['comment']          = $request_info['comment'];
        $data['date_added']       = date($this->language->get('date_format_short'), strtotime($request_info['date_added']));

        $data['status'] = isset($this->request->post['status'])
            ? (int)$this->request->post['status']
            : (int)$request_info['status'];

        $data['admin_comment'] = isset($this->request->post['admin_comment'])
            ? $this->request->post['admin_comment']
            : $request_info['admin_comment'];

        $data['action'] = $this->url->link('extension/module/price_match/view', 'user_token=' . $this->session->data['user_token'] . '&price_match_id=' . $price_match_id, true);
        $data['back']   = $this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        // Language strings for the view template
        $view_lang_keys = array(
            'heading_title', 'text_view', 'text_pending', 'text_approved', 'text_rejected',
            'label_product', 'label_customer', 'label_email', 'label_telephone',
            'label_competitor_name', 'label_competitor_url', 'label_competitor_price',
            'label_status', 'label_date_added', 'label_admin_comment',
            'entry_comment', 'button_save', 'button_back',
        );
        foreach ($view_lang_keys as $key) {
            $data[$key] = $this->language->get($key);
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/price_match_view', $data));
    }

    // -------------------------------------------------------------------------
    // Delete Request(s)
    // -------------------------------------------------------------------------
    public function delete() {
        $this->load->language('extension/module/price_match');
        $this->load->model('extension/module/price_match');

        if (isset($this->request->post['selected']) && $this->validate()) {
            foreach ($this->request->post['selected'] as $price_match_id) {
                $this->model_extension_module_price_match->deleteRequest((int)$price_match_id);
            }
            $this->session->data['success'] = $this->language->get('text_success_delete');
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        }

        $this->response->redirect($this->url->link('extension/module/price_match/requests', 'user_token=' . $this->session->data['user_token'], true));
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------
    public function install() {
        $this->load->model('extension/module/price_match');
        $this->model_extension_module_price_match->install();
    }

    public function uninstall() {
        $this->load->model('extension/module/price_match');
        $this->model_extension_module_price_match->uninstall();
    }

    // -------------------------------------------------------------------------
    // Validate
    // -------------------------------------------------------------------------
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/price_match')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
