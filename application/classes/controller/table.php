<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Controller <b>Table</b> 
 */
class Controller_Table extends Controller_Data {

    protected $sub_navis = array();
    protected $id_hs = null;
    protected $filter = '________________________________';

    public function before() {

        parent::before();
        $this->sub_navis = array(
            'index' => __('New'),
            'top' => __('Top'),
            'times' => __('Times'),
            'themes' => __('Themes'),
            'names' => __('Names')
        );
        $index = Arr::get($this->session->get('action'), 'name', 'index');
        $this->sub_navi->activate($this->sub_navis[$index]);
        $this->id_hs = $this->request->param('id');
        $this->filter = $this->request->param('filter', $this->filter);
        $this->session->set('referrer', $this->request->uri());
      
    }

    public function set_filter($filters) {
        if (count($filters) > 0) {

            foreach ($filters as $filter) {
                if ($filter !== "-1") {
                    $filter = explode('_', $filter);
                    $code = $filter[0];
                    $pos = $filter[1];
                    $len = $filter[2];
                    $c = 0;
                    for ($i = 0; $i < strlen($this->filter); $i++) {
                        if ($i == $pos - 1) {
                            for ($l = $i; $l < $pos + $len - 1; $l++) {
                                $this->filter[$l] = $code[$c];
                                $c++;
                            }
                        }
                    }
                }
            }
        }
    }

    public function action_details() {
          
          
        if (!$this->id_hs)
            throw new HTTP_Exception_404();
        
        $keymask = ORM::factory('keymask', $this->id_hs);
        
        if ($this->user->has_roles(array('guest')) && $keymask->project->Zugangsklasse !== "-1")
            $this->request->redirect(I18n::$lang . '/auth/login');
        
        $this->scripts[] = 'table.js';
        $view = View::factory(I18n::$lang . '/table/details');


        $list = View::factory(I18n::$lang . '/project/list');
        //assign new projects to subview
        $list->projects = $keymask->project;
        $this->project = $keymask->project->Projektname;
        
        //assign the referrer uri
        $list->uri = URL::site(I18n::$lang . '/table/details/' . $this->id_hs);

        $post = $this->request->post('filter');

        $this->set_filter($post);

        $details = $keymask->getDetails($this->filter);

        $data = null;

        if (!$post) {
            $post = array();
            $i = 0;
            foreach (Arr::get($details, 'filters', array()) as $filters) {
                foreach ($filters as $key => $filter) {
                    $f = explode('_', $key);
                    $code = $f[0];
                    $pos = $f[1];
                    $len = $f[2];
                    if (substr($this->filter, $pos - 1, $len) === $code) {
                        $post[$i] = $key;
                    } else {
                        $post[$i] = "-1";
                    }
                }
                $i++;
            }
        }

        if (count(Arr::get($details, 'keys', array())) <= $this->config->get('max_timelines')) {
            $data = $keymask->getData($this->filter);
        }


        $view->details = $details['details'];
        $view->keys = $details['keys'];
        $view->data = $data;
        $view->keymask = $keymask;
       
        $view->tables = $details['tables'];
        $view->titles = $details['titles'];
        $view->filters = $details['filters'];
        $view->sources = $details['sources'];
        $view->notes = $details['notes'];
        $view->filter = $this->filter;
        $view->is_admin = $this->user->has_roles(array('admin'));
        $view->post = $post;
        $view->guest = $this->user->has_roles(array('guest'));
        $view->search = strstr($this->request->referrer(), 'search');
        $view->project = $list->render();
        $this->content = $view->render();
    }

    public function action_edit_details() {
        if (!$this->id_hs && !$this->user->has_roles(array('admin')))
            throw new HTTP_Exception_404();
        $keymask = ORM::factory('keymask', $this->id_hs);
        $this->scripts[] = 'table.js';
        $view = View::factory(I18n::$lang . '/table/details_admin');


        $list = View::factory(I18n::$lang . '/project/list');
        //assign new projects to subview
        $list->projects = $keymask->project;
        $this->project = $keymask->project->Projektname;
        //assign the referrer uri
        $list->uri = URL::site(I18n::$lang . '/table/details/' . $this->id_hs);

        $post = $this->request->post('filter');

        $this->set_filter($post);

        $details = $keymask->getDetails($this->filter);

        $data = null;

        if (!$post) {
            $post = array();
            $i = 0;
            foreach (Arr::get($details, 'filters', array()) as $filters) {
                foreach ($filters as $key => $filter) {
                    $f = explode('_', $key);
                    $code = $f[0];
                    $pos = $f[1];
                    $len = $f[2];
                    if (substr($this->filter, $pos - 1, $len) === $code) {
                        $post[$i] = $key;
                    } else {
                        $post[$i] = "all";
                    }
                }
                $i++;
            }
        }

        if (count(Arr::get($details, 'keys', array())) <= $this->config->get('max_timelines')) {
            $data = $keymask->getData($this->filter);
        }


        $view->details = $details['details'];
        $view->keys = $details['keys'];
        $view->data = $data;
        $view->keymask = $keymask;

        $view->tables = $details['tables'];
        $view->titles = $details['titles'];
        $view->filters = $details['filters'];
        $view->sources = $details['sources'];
        $view->notes = $details['notes'];
        $view->filter = $this->filter;
        $view->is_admin = $this->user->has_roles(array('admin'));
        $view->post = $post;
        $view->search = strstr($this->request->referrer(), 'search');
        $view->project = $list->render();
        $this->content = $view->render();
    }

    public function action_download() {
           $keymask = ORM::factory('keymask', $this->id_hs);
            if ($this->user->has_roles(array('guest')) && $keymask->project->Zugangsklasse !== "-1")
            $this->request->redirect(I18n::$lang . '/auth/login');
            
        if (HTTP_Request::POST == $this->request->method()) {
         
            $details = $keymask->getDetails($this->filter);
            $name = $keymask->project->Projektname;
            $type = $this->request->param('type');
            $uses = $this->request->post('uses');
            if ($uses == '-1')
                $uses = $this->request->post('custom');


            $download = ORM::factory('download');
            $download->username = $this->user->username;
            $download->projekt_id = $keymask->project->ID_Projekt;
            $download->anzahl = count($details['keys']);
            $download->type = $type;
            $download->intended_use = $uses;
            $download->za_nummer = $keymask->project->ZA_Studiennummer;
            $download->name = $name;
            $download->mkdate = time();
            $download->create();
            if (Kohana::$environment === Kohana::PRODUCTION) {
                $url = URL::base('http') . I18n::$lang . '/table/' . $type . '/' . $this->id_hs . '/' . $this->filter;
            } else {
                $url = URL::site(I18n::$lang . '/table/' . $type . '/' . $this->id_hs . '/' . $this->filter, 'http');
            }

            $this->request->redirect('http://www.etracker.de/lnkcnt.php?et=qPKGYV&url=' . urlencode($url) . '&lnkname=' . urlencode('HISTAT/download/' . $name));
        }
    }

    protected function studip_utf8decode($string) {
        if (!preg_match('/[\200-\377]/', $string)) {
            return $string;
        } else {
            $windows1252 = array(
                "\x80" => '&#8364;',
                "\x81" => '&#65533;',
                "\x82" => '&#8218;',
                "\x83" => '&#402;',
                "\x84" => '&#8222;',
                "\x85" => '&#8230;',
                "\x86" => '&#8224;',
                "\x87" => '&#8225;',
                "\x88" => '&#710;',
                "\x89" => '&#8240;',
                "\x8A" => '&#352;',
                "\x8B" => '&#8249;',
                "\x8C" => '&#338;',
                "\x8D" => '&#65533;',
                "\x8E" => '&#381;',
                "\x8F" => '&#65533;',
                "\x90" => '&#65533;',
                "\x91" => '&#8216;',
                "\x92" => '&#8217;',
                "\x93" => '&#8220;',
                "\x94" => '&#8221;',
                "\x95" => '&#8226;',
                "\x96" => '&#8211;',
                "\x97" => '&#8212;',
                "\x98" => '&#732;',
                "\x99" => '&#8482;',
                "\x9A" => '&#353;',
                "\x9B" => '&#8250;',
                "\x9C" => '&#339;',
                "\x9D" => '&#65533;',
                "\x9E" => '&#382;',
                "\x9F" => '&#376;');
            return str_replace(array_values($windows1252), array_keys($windows1252), utf8_decode(mb_encode_numericentity($string, array(0x100, 0xffff, 0, 0xffff), 'UTF-8')
                            )
            );
        }
    }

    public function action_xls() {
        $keymask = ORM::factory('keymask', $this->id_hs);
           if ($this->user->has_roles(array('guest')) && $keymask->project->Zugangsklasse !== "-1")
            $this->request->redirect(I18n::$lang . '/auth/login');

        $table_name = substr(str_replace(array('"', ':', ' ', '/', '\\', '.', ','), '_', $keymask->Name), 0, 100);
        $details = $keymask->getDetails($this->filter);
        $details['data'] = null;
        $grid = array();
        if (count(Arr::get($details, 'keys', array())) <= $this->config->get('max_timelines')) {
            $details['data'] = $keymask->getData($this->filter);
        }
        if ($details['data']) {

            $grid = $this->create_grid($details);
            $grid[1] = array($table_name);
            $ws = new Spreadsheet();
            $ws->set_active_sheet(0);
            $ws->set_data($grid);
            $ws->send(array('name' => ($table_name), 'format' => 'Excel5'));
        }
    }

    public function action_xlsx() {
        $keymask = ORM::factory('keymask', $this->id_hs);
           if ($this->user->has_roles(array('guest')) && $keymask->project->Zugangsklasse !== "-1")
            $this->request->redirect(I18n::$lang . '/auth/login');
        $table_name = substr(str_replace(array('"', ':', ' ', '/', '\\', '.', ','), '_', $keymask->Name), 0, 100);
        $details = $keymask->getDetails($this->filter);
        $details['data'] = null;
        $grid = array();
        if (count(Arr::get($details, 'keys', array())) <= $this->config->get('max_timelines')) {
            $details['data'] = $keymask->getData($this->filter);
        }
        if ($details['data']) {

            $grid = $this->create_grid($details);
            $grid[1] = array($table_name);
            $ws = new Spreadsheet();
            $ws->set_active_sheet(0);
            $ws->set_data($grid);
            $ws->send(array('name' => ($table_name), 'format' => 'Excel2007'));
        }
    }

    public function action_csv() {
        $keymask = ORM::factory('keymask', $this->id_hs);
           if ($this->user->has_roles(array('guest')) && $keymask->project->Zugangsklasse !== "-1")
            $this->request->redirect(I18n::$lang . '/auth/login');
        $table_name = substr(str_replace(array('"', ':', ' ', '/', '\\', '.', ','), '_', $keymask->Name), 0, 100);
        $details = $keymask->getDetails($this->filter);
        $details['data'] = null;
        $grid = array();
        if (count(Arr::get($details, 'keys', array())) <= $this->config->get('max_timelines')) {
            $details['data'] = $keymask->getData($this->filter);
        }
        if ($details['data']) {

            $grid = $this->create_grid($details);
            $grid[1] = array($table_name);
            $ws = new Spreadsheet();
            $ws->set_active_sheet(0);

            $ws->set_data($grid);
            $path = $ws->send(array('name' => $table_name, 'format' => 'CSV'));
        }
    }

    protected function create_grid($details) {
        $grid = array();
        $i = 2;
        $headers = Arr::get($details, 'details', array());
        $tables = Arr::get($details, 'tables', array());
        $sources = Arr::get($details, 'sources', array());
        $data = Arr::get($details, 'data', array());
        $keys = Arr::get($details, 'keys', array());
        $notes = Arr::get($details, 'notes', array());
        foreach ($headers as $header) {
            $row = array();
            $key = array_keys($header);
            $row[] = $header[$key[0]]->CodeBeschreibung;
            foreach ($keys as $k) {
                $row[] = $header[$k]->CodeBezeichnung;
            }
            $grid[$i] = $row;
            $i++;
        }
        if (count(array_filter($tables)) > 0) {
            $row = array();
            $row[] = 'Tabelle';
            foreach ($keys as $k) {
                $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $tables[$k]);
            }
            $grid[$i] = $row;
            $i++;
        }
        if (count(array_filter($sources)) > 0) {
            $row = array();
            $row[] = 'Quellen';
            foreach ($keys as $k) {
                $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $sources[$k]);
            }
            $grid[$i] = $row;
            $i++;
        }
        if (count(array_filter($notes)) > 0) {
            $row = array();
            $row[] = 'Anmerkungen';
            foreach ($keys as $k) {
                $row[] = str_replace(array("\r\n", "\n", "\r"), ' ', $notes[$k]);
            }
            $grid[$i] = $row;
            $i++;
        }
        foreach ($data as $y => $d) {
            $row = array();
            $row[] = $y;

            foreach ($keys as $k) {
                $newData = Arr::get($d, $k, array('data' => '', 'note' => NULL));
                if (Arr::get($newData, 'note')) {
                    $row[] = $newData;
                } else {
                    $row[] = Arr::get($newData, 'data');
                }
            }
            $grid[$i] = $row;
            $i++;
        }
        return $grid;
    }

    public function action_edit() {
        $this->auto_render = false;
        if ($this->session->get('xsrf') == $this->request->post('xsfr') && $this->user->has_roles(array('admin'))) {
            $value = NULL;
            $result = false;
            $id_hs = $this->request->post('id_hs');
            $key = $this->request->post('key');
            $year = $this->request->post('year');
            $value = $this->request->post('value');
            $id_projekt = $this->request->post('id_projekt');

            $select = DB::select('Data')
                            ->from('Daten__Aka')
                            ->where('ID_HS', '=', $id_hs)
                            ->where('Schluessel', '=', $key)
                            ->where('Jahr_Sem', '=', $year)->execute();

            $db = DB::delete('Daten__Aka')
                    ->where('ID_HS', '=', $id_hs)
                    ->where('Schluessel', '=', $key)
                    ->where('Jahr_Sem', '=', $year);


            if ($db->execute())
                $result = true;

            if ($value) {
                $result = false;
                $db = DB::insert('Daten__Aka', array('Data', 'ID_HS', 'Schluessel', 'Jahr_Sem'))
                        ->values(array($value, $id_hs, $key, $year));
                if ($db->execute())
                    $result = true;
            }

            if ($result) {

                $logs = ORM::factory('tablelog');
                $logs->ID_Projekt = $id_projekt;
                $logs->ID_HS = $id_hs;
                $logs->Schluessel = $key;
                $logs->Jahr_Sem = $year;
                $logs->username = $this->user->username;
                $logs->old_value = Arr::get($select[0], 'Data');
                $logs->new_value = $value;

                $logs->save();
                $keymask = ORM::factory('keymask', $id_hs);
                $details = $keymask->getDetails($key);
                $filter_text = implode(',', Arr::get($details['titles'], $key, array()));


                $view = View::factory('de/mails/datachange');
                $view->projectname = $keymask->project->Projektname;
                $view->tablename = $keymask->Name;
                $view->filter = $filter_text;
                $view->year = $year;
                $view->old_value = Arr::get($select[0], 'Data', 'leer');
                $view->new_value = $value == NULL ? 'leer' : $value;
                $view->username = $this->user->surname . ' ' . $this->user->name;
                $view->chdate = date('Y-m-d H:i:s');
                $view->id_hs = $id_hs;
                $view->filter_key = $key;
                $to = $this->config->get('log_to');

                $email = Email::factory(__('Änderungen an Daten'))
                        ->to($to)
                        ->from($this->config->get('from'))
                        ->message($view->render(), 'text/html')
                        ->send();
            }
            $this->response->body(json_encode(array('result' => $result)));
        } else {
            $this->response->body(json_encode(array('result' => false)));
        }
    }

    public function action_edit_header() {
        $this->auto_render = false;
        if ($this->session->get('xsrf') == $this->request->post('xsfr') && $this->user->has_roles(array('admin'))) {
            $value = NULL;
            $result = false;
            $id_hs = $this->request->post('id_hs');
            $key = $this->request->post('key');
            $type = $this->request->post('type');
            $value = $this->request->post('value');
            $id_projekt = $this->request->post('id_projekt');
            $types = array(
                'source' => 'Quelle',
                'note' => 'Anmerkung',
                'table' => 'Tabelle'
            );


            if ($value && $type) {
                $select = DB::select('Quelle', 'Anmerkung', 'Tabelle')
                                ->from('Lit_ZR')
                                ->where('ID_HS', '=', $id_hs)
                                ->where('Schluessel', '=', $key)->execute();
                $db = DB::update('Lit_ZR')->set(array($types[$type] => $value))
                        ->where('ID_HS', '=', $id_hs)
                        ->where('Schluessel', '=', $key);
                if ($db->execute()) {


                    $logs = ORM::factory('tableheadlog');
                    $logs->ID_Projekt = $id_projekt;
                    $logs->ID_HS = $id_hs;
                    $logs->Schluessel = $key;
                    $logs->head = $types[$type];
                    $logs->username = $this->user->username;
                    $logs->old_value = Arr::get($select[0], $types[$type]);
                    $logs->new_value = $value;

                    $logs->save();


                    $keymask = ORM::factory('keymask', $id_hs);
                    $details = $keymask->getDetails($key);
                    $filter_text = implode(',', Arr::get($details['titles'], $key, array()));


                    $view = View::factory('de/mails/dataheadchange');
                    $view->projectname = $keymask->project->Projektname;
                    $view->tablename = $keymask->Name;
                    $view->filter = $filter_text;
                    $view->head = $types[$type];
                    $view->old_value = Arr::get($select[0], $types[$type], 'leer');
                    $view->new_value = $value == NULL ? 'leer' : $value;
                    $view->username = $this->user->surname . ' ' . $this->user->name;
                    $view->chdate = date('Y-m-d H:i:s');
                    $view->id_hs = $id_hs;
                    $view->filter_key = $key;
                    $to = $this->config->get('log_to');

                    $email = Email::factory(__('Änderungen an Kopfbereich der Tabelle'))
                            ->to($to)
                            ->from($this->config->get('from'))
                            ->message($view->render(), 'text/html')
                            ->send();
                    $result = true;
                }
            }
            $this->response->body(json_encode(array('result' => $result)));
        } else {
            $this->response->body(json_encode(array('result' => false)));
        }
    }

}