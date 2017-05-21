<?php

class ControllerExtensionModuleRedirects extends Controller {

	private $error = array();

	public function index() {

//      Not using this - no language file set up yet.
        $this->load->language('extension/module/redirects');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title']  = $this->language->get('heading_title');
        $data['text_edit']      = $this->language->get('text_edit');

		$data['old_url']        = $this->language->get('old_url');
        $data['new_url']        = $this->language->get('new_url');

        $data['old_placeholder'] = $this->language->get('old_placeholder');
        $data['new_placeholder'] = $this->language->get('new_placeholder');

        $data['btn_new_row']    = $this->language->get('new_redirect');
        $data['btn_del_row']    = $this->language->get('delete_redirect');

        $this->load->model('extension/seo/redirects');

        $data['redirects']      = $this->model_extension_seo_redirects->listAllRedirects();

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');


        // somehow this should show a success message - need to figure that out!
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('redirects', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=feed', true));
        }


        // Likewise this SHOULD send an error back to the page
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

		$data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/redirects', 'token=' . $this->session->data['token'], true)
		);

//        picked this out of the product.php controller - need to implement for pagination of this big list
//        $pagination = new Pagination();
//        $pagination->total = $product_total;
//        $pagination->page = $page;
//        $pagination->limit = $this->config->get('config_limit_admin');
//        $pagination->url = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url . '&page={page}', true);

		$data['action'] = $this->url->link('extension/module/redirects/saveRedirects', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/redirects', $data));

	}

    public function saveRedirects() {
        // if page is accessed without posted redirects in the $_POST array, return to the redirects page
        // TODO: post a feedback message that no changes were made
        if(!isset($_POST['redirect'])) {
            $this->response->redirect($this->url->link('extension/module/redirects', 'token=' . $this->session->data['token'], true));
            exit();
        }

        $redirects = $_POST['redirect'];

        // first make sure that each entry will pass validation
        foreach ($redirects as $redirect) {
            $data['old_url'] = $this->validateURL($redirect['old_url']);
            $data['new_url'] = $this->validateURL($redirect['new_url']);
            if(!$data['old_url'] || !$data['new_url']){
                // failed the validation rules!
                // do not proceed with script, because old entries would be deleted
                // TODO: retain bad entries when we return to the home page and notify user of this
                // NOTE: most validation is done in the front end currently.
                $this->response->redirect($this->url->link('extension/module/redirects', 'token=' . $this->session->data['token'], true));
                exit();
            }
        }

        // passed validation, lets update the redirect table
        $this->load->model('extension/seo/redirects');
        $this->model_extension_seo_redirects->deleteAllRedirects();

        foreach ($redirects as $data) {
            $this->model_extension_seo_redirects->saveRedirect($data);
        }

        $this->response->redirect($this->url->link('extension/module/redirects', 'token=' . $this->session->data['token'], true));

    }

    private function validateURL($data){
        // NOTE: most validation is done in the front end currently, but we do check for length and strip leading and trailing spaces and slashes
        // 1. check for illegal characters (we should throw error if there is a space in the middle of a string);

        if(strlen($data) > 255) {
            return false;
        }
        trim($data, ' /');
        return $result;
    }

	protected function validate() {

		if (!$this->user->hasPermission('modify', 'extension/module/redirects')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}