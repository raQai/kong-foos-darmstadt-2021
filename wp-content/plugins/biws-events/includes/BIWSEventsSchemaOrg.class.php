<?php

defined('ABSPATH') or die('Nope!');

if (!class_exists('BIWS_EventsSchemaOrg')) {
    class BIWS_EventsSchemaOrg
    {
        private $schema = array();
        private $graphs = array();

        public function __construct()
        {
        }

        function addGraphElement($arr)
        {
            $this->graphs[] = $arr;
        }

        function setSchema($arr)
        {
            $this->schema = $arr;
        }

        function serialize()
        {
            $script = '<script type="application/ld+json">';
            if ($this->graphs) {
                $script .= '{';
                $script .= '"@context":"http://schema.org",';
                $script .= '"@graph":[';
                $numItems = count($this->graphs);
                $i = 0;
                foreach ($this->graphs as $graph) {
                    $script .= $this->serializeArray($graph);
                    if (++$i !== $numItems) {
                        $script .= ',';
                    }
                }
                $script .= ']';
                $script .= '}';
            } else {
                $this->schema['@context'] = 'http://schema.org';
                $script .= $this->serializeArray($this->schema);
            }
            $script .= '</script>';
            return $script;
        }

        private function serializeArray($arr)
        {
            $string = "";
            if ($arr) {
                $string .= '{';
                $numItems = count($arr);
                $i = 0;
                foreach ($arr as $key => $value) {
                    $string .= '"' . $key . '":' . (is_array($value) ? $this->serializeArray($value) : '"' . $value . '"');
                    if (++$i !== $numItems) {
                        $string .= ',';
                    }
                }
                $string .= '}';
            }
            return $string;
        }
    }
}
