<?php

namespace sitemap;
use \Exception;
use \DOMDocument;
error_reporting(E_ERROR | E_PARSE);



    class Sitemap
    {
        private $path;
        private $param ="loc;lastmod;priority;changefreq\n";


        public function __construct($create_data, $type, $path)
        {
            $this->path = $path;
            $type = strtolower($type);
            $path_lower = pathinfo(strtolower($path), PATHINFO_EXTENSION);

            try{
                //Проверка на массив
                if(!is_array($create_data))
                {
                    throw new Exception("An array is expected");
                }
                //Проверка расширения файла
                if($type != $path_lower)
                    {
                        throw new Exception("Wrong path");
                    }
                for ($i=0;$i<count($create_data);$i++)
                {

                    if(count($create_data[$i]) != 4) throw new Exception("Array size is not correct");
                }


            switch ($type){
                case 'xml':
                    $this->createXml($create_data);
                    break;
                case 'csv':
                    $this->createCsv($create_data);
                    break;
                case 'json':
                    $this->createJson($create_data);
                    break;
                default:
                    throw new Exception("The wrong file type is selected");
                                }
            }catch (Exception $e){
                echo $e->getMessage();
                die();
            }

        }

        public function createXml($create_data)
        {

            $xml=new DomDocument('1.0', 'utf-8');
            $urlset = $xml->appendChild($xml->createElement('urlset'));
            $urlset->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
            $urlset->setAttribute('xmlns','http://www.sitemaps.org/schemas/sitemap/0.9');
            $urlset->setAttribute('xsi:schemaLocation','http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

            foreach ($create_data as $value)
            {
                $url = $urlset->appendChild($xml->createElement('url'));
                $loc = $url->appendChild($xml->createElement('loc'));
                $loc->appendChild($xml->createTextNode($value['loc']));
                $lastmod = $url->appendChild($xml->createElement('lastmod'));
                $lastmod->appendChild($xml->createTextNode($value['lastmod']));
                $priority = $url->appendChild($xml->createElement('priority'));
                $priority->appendChild($xml->createTextNode($value['priority']));
                $changefreq = $url->appendChild($xml->createElement('changefreq'));
                $changefreq->appendChild($xml->createTextNode($value['changefreq']));

            }


            $xml->formatOutput = true;
            $out = $xml->saveHTML();
            $this->saveFile($out);

        }

        public function createCsv($create_data)
        {
            $out = $this->param;

            foreach ($create_data as $values)
            {

                foreach ($values as $key=> $value)
                {
                    if($key == 'changefreq')
                    {
                        $out.=$value."\n";
                    }
                    else
                    {
                        $out.=$value.";";
                    }

                }
            }

            $this->saveFile($out);

        }

        public function createJson($create_data)
        {


            $out = json_encode($create_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES| JSON_NUMERIC_CHECK);
            $out=str_replace('"loc"',"loc",$out);
            $out=str_replace('"lastmod"',"lastmod",$out);
            $out=str_replace('"priority"',"priority",$out);
            $out=str_replace('"changefreq"',"changefreq",$out);

            $this->saveFile( $out);

        }

        //Сохранение файла
        public function saveFile($out)
        {
            $path=$this->path;

            if ($path[0] == "/") $path = substr($path, 1);
            //Получение директории
            $dir = substr($path, 0, strrpos($path, '/', -2)+1);
            //Проверка на наличие директории
            if(!is_dir($dir) && strpos($dir, "/"))
            {
                if(!mkdir($dir, 0700, true))  throw new Exception("Failed to create directory");
            }

            if (!is_writable($path) && !fopen($path, 'w')) throw new Exception("File access denied");


            $fp = fopen($path, 'w');
            fwrite($fp, $out);
            fclose($fp);
        }

    }