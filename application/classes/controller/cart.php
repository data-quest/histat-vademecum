<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Cart extends Controller_Table {

    public function before() {
        parent::before();
        $this->sub_navi->activate();
    }

    public function action_index() {
        $view = View::factory(I18n::$lang . '/cart/index');
     

        $filters = array();
        $projects = array();
        $tables = array();
     
        foreach ($this->user->getCartItems() as $item) {   
        
            $bearbeitung = '';
            $datum = substr($item->Datum_der_Bearbeitung, -4);
            if (!empty($datum)) {
                $bearbeitung = '[' . $datum . ']';
            }
            $projectName = __(':author, (:pub_year :edit_year) :project', array(':author' => $item->Projektautor,
                ':pub_year' => $item->Publikationsjahr,
                ':edit_year' => $bearbeitung,
                ':project' => $item->Projektname
                    ));
            $filters[$item->ID_Projekt][$item->ID_HS][$item->filter] = array('text' =>  json_decode($item->filter_text), 'timelines' => $item->timelines);
            $projects[$item->ID_Projekt] = array('name' => $projectName,
                'za' => $item->ZA_Studiennummer,
                'theme' => $item->Thema
            );

            $tables[$item->ID_Projekt][$item->ID_HS] = $item->Name;
        }
      
 
     
        $view->message = $this->request->param('id', FALSE);
        $view->projects =$projects;
        $view->tables = $tables;
        $view->filters = $filters;
        $view->options = Kohana::$config->load('download')->get('options');
        $this->content = $view->render();
    }

    public function action_delete() {

        $cart = ORM::factory('cart')
                ->where('user_id', '=', $this->user->id)
                ->where('ID_HS', '=', $this->id_hs)
                ->where('filter', '=', $this->filter)
                ->find();
        if ($cart->loaded()) {
            $cart->delete();
            $this->request->redirect(I18n::$lang . '/cart/index/success');
        }
        $this->request->redirect(I18n::$lang . '/cart/index/error');
    }

    public function action_delete_selected() {
        $selected =  $this->request->post('selected');
        if($selected){
        $result = DB::delete('warenkorb')
                ->where('user_id', '=', $this->user->id)
                ->where(DB::expr('CONCAT_WS("/",ID_HS,filter)'), 'IN', DB::expr("('" . implode("','", $selected) . "')"))
                ->execute();


        if ($result) {

            $this->request->redirect(I18n::$lang . '/cart/index/success');
        }
        }
        $this->request->redirect(I18n::$lang . '/cart/index/error');
    }

    public function action_add() {
        if (HTTP_Request::POST != $this->request->method() && !$this->request->is_ajax() && $this->request->is_initial()) {
            throw new HTTP_Exception_404();
        }
        $this->auto_render = FALSE;
        $this->id_hs = $this->request->post('id');
        $this->filter = $this->request->post('filter');

        $filter_text = json_encode($this->request->post('filter_text'));

        $cart = $this->user->cart_items
                ->where('keymask.ID_HS', '=', $this->id_hs)
                ->where('filter', '=', $this->filter)
                ->find_all();

        if ($cart->count() == 0) {
            $cart = ORM::factory('cart');
            $cart->ID_HS = $this->id_hs;
            $cart->filter = $this->filter;
            $cart->timelines = $this->request->post('timelines');
            $cart->filter_text = $filter_text;
            $cart->user_id = $this->user->id;
            $cart->chdate = time();
            $cart->create();
            echo true;
        }
        echo false;
        //  $this->request->redirect(I18n::$lang . '/table/details/' . $this->id_hs . '/' . $this->filter);
    }

    public function action_download_selected() {

        if (HTTP_Request::POST != $this->request->method() || $this->request->post('selected') == NULL) {
            $this->request->redirect(I18n::$lang . '/cart/index');
        }
        $cart = ORM::factory('cart')
                ->where('user_id', '=', $this->user->id)
                ->where(DB::expr('CONCAT_WS("/",cart.ID_HS,filter)'), 'IN', DB::expr("('" . implode("','", $this->request->post('selected')) . "')"))
                ->find_all();
        $formats = array(
            'xsl' => 'Excel5',
            'xslx' => 'Excel2007',
            'csv' => 'CSV'
        );
        if (!is_dir('/tmp/histat/')) {
            mkdir('/tmp/histat/', 0777, true);
        }
        if (!is_dir('/tmp/histat/download_' . $this->user->id)) {
            mkdir('/tmp/histat/download_' . $this->user->id);
        }
        $uses = $this->request->post('uses');
        if ($uses == '-1')
            $uses = $this->request->post('custom');





        foreach ($cart as $nr => $item) {

            $keymask = ORM::factory('keymask', $item->ID_HS);
                   $table_name = substr(str_replace(array('"',':',' ','/','\\','.') , '_',  $keymask->Name),0,100);
            $details = $keymask->getDetails($item->filter);
            $details['data'] = null;
            $names = array();
            if (count(Arr::get($details, 'keys', array())) <= $this->config->get('max_timelines')) {
                $details['data'] = $keymask->getData($item->filter);
            }
            $download = ORM::factory('download');
            $download->username = $this->user->username;
            $download->projekt_id = $keymask->project->ID_Projekt;
            $download->anzahl = count($details['keys']);
            $download->type =  $this->request->post('format');
            $download->intended_use = $uses;
            $download->za_nummer = $keymask->project->ZA_Studiennummer;
            $download->name = $keymask->project->Projektname;
            $download->mkdate = time();
            $download->create();

            if ($details['data']) {
                
                $grid = $this->create_grid($details);
                $grid[1] = array($table_name);
                $ws = new Spreadsheet();
                $ws->set_active_sheet(0);
                $ws->set_data($grid);
                $name = iconv('UTF-8', 'ASCII//TRANSLIT',$table_name . '-' . $nr);
                $name = $ws->save(array('name' => $name, 'format' => Arr::get($formats, $this->request->post('format'), 'Excel2007'), 'path' => '/tmp/histat/download_' . $this->user->id . '/'));
            }
        }
        $path = '/tmp/histat/';


        $command = sprintf("cd /tmp/histat/ ;zip -r download_%d.zip  download_%d/", $this->user->id, $this->user->id);

        exec($command);

        $this->rrmdir('/tmp/histat/download_' . $this->user->id . '/');


        $this->response->send_file('/tmp/histat/download_' . $this->user->id . '.zip', sprintf("vademecum.gesis.org_Warenkorb_%s.zip", date("m-d-y-h-i", time())), array('delete' => TRUE));
    }

    private function rrmdir($dir) {
        $fp = opendir($dir);
        if ($fp) {
            while ($f = readdir($fp)) {
                $file = $dir . "/" . $f;
                if ($f == "." || $f == "..") {
                    continue;
                } else if (is_dir($file) && !is_link($file)) {
                    rrmdir($file);
                } else {
                    unlink($file);
                }
            }
            closedir($fp);
            rmdir($dir);
        }
    }

}

