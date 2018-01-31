<?php
//общий класса для формирования отображени
class view extends Singleton {

    public $view;           //массив общего отображения
    public $out;            //полное построение html кода
    public $content='';     //основной контент
    public $title;          //заголовок страницы



    //инициализация шаблонна страницы (можно и не делать а сделать это с своем контроллере)
    public function pageDefault () {
        $this->setHeader();
        $this->setUpBody();
        $this->setDownBody();
        $this->setHtmlHead();
        }//end pageDefault

    public function setHtmlHead () {
        $this->view['html']['down'] = '</html>';
        $this->view['html']['head'] = '<!DOCTYPE html>';
        }//end setHtmlHead

    //поспроение заголовка шаблонной страницы
    public function setHeader () {
        $this->view['header']['up']             = '<head>' ;
        $this->view['header']['charset']        = '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        $this->view['header']['description']    = '<meta name="description" content="'.Core::gi()->config['head']['description']['text'].'"/>';
        $this->view['header']['keywords']       = '<meta name="keywords" content="'.Core::gi()->config['head']['keywords']['text'].'"/>';
        $this->view['header']['ico']            = '<link type="image/gif" rel="shortcut icon" href="'.BASIC_URL_FULL.'favicon.gif" />';
        $this->view['header']['title']          = '<title>'.Core::gi()->config['head']['title']['text'].'</title>';
        $this->view['header']['down']           = '</head>';

        //css - внешние модули
        foreach (Core::gi()->config['head']['ext']['css'] as $cssExtName => $ext) {
            //если внутри несколько css то
            if (is_array($ext['directory'])) {
                for ($i=0;$i<count($ext['directory']);$i++)
                    $this->view['header']['css'] .= '<link rel="stylesheet" type="text/css" href='.BASIC_URL_FULL.MEXT.$ext['directory'][$i].'>';
                }
            //если один css то
            else {
                $this->view['header']['css'] .= '<link rel="stylesheet" type="text/css" href='.BASIC_URL_FULL.MEXT.$ext['directory'].'>';
                }
            }//end nameCSS


        //js - внешние модули
        foreach (Core::gi()->config['head']['ext']['js'] as $jsExtName => $ext) {
            //если внутри несколько css то
            if (is_array($ext['directory'])) {
                for ($i=0;$i<count($ext['directory']);$i++)
                    $this->view['header']['js'] .= '<script type="text/javascript" src="'.BASIC_URL_FULL.MEXT.$ext['directory'][$i].'"></script>';
                }
            //если один css то
            else {
                $this->view['header']['js'] .= '<script type="text/javascript" src="'.BASIC_URL_FULL.MEXT.$ext['directory'].'"></script>';
                }
            }//end nameCSS


        //построение полного header
        if (isset($this->title) && !$this->title=='') {
            $this->view['header']['title'] = '<title>'.$this->title.'</title>';
        }
        $this->view['headerBuild'] = ''.
            $this->view['header']['up'].
            $this->view['header']['ico'].
            $this->view['header']['charset'].
            $this->view['header']['description'].
            $this->view['header']['keywords'].
            $this->view['header']['title'].
            $this->view['header']['css'].
            $this->view['header']['js'].
            $this->view['header']['down'];

        }//end setHeader

    //построение заголовка тела шаблонной страницы
    public function setUpBody () {
        $this->view['body']['up'] = "<body>";
        }

    //построение подвала тела шаблонной страницы
    public function setDownBody () {
        $this->view['body']['down'] = "</body>";
        }

    public function getPage(){
        if (View::gi()->content!='NO') {
            View::gi()->pageDefault();
            echo View::gi()->view['html']['head'];
            echo View::gi()->view['headerBuild'];
            echo View::gi()->view['body']['up'];
            echo View::gi()->content;
            echo View::gi()->view['body']['down'];
        }
    }

    public function show($filename='',$mass_array=[],$show=TRUE,$content=FALSE) {
        if (count($mass_array)>=1) {
            foreach ($mass_array as $key => $value) {
                //продумать что бы структура переменных не нарушалась
                $$key = $value;
            }

        }

        if ((isset($filename) && $filename!='') && file_exists(LVIEW.$filename.'.php') ) {
                if(!$show) {
                    require_once(LVIEW . $filename.'.php');
                } else {
                    ob_start();
                    require_once(LVIEW . $filename.'.php');
                    $out = ob_get_contents();
                    ob_end_clean();
                    if (!$content) {
                        View::gi()->content = $out;
                        return true;
                    } else {
                        return $out;
                    }
                }
            } else {
            return FALSE;
        }
    }


}//end class view