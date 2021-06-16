<?php

    class Test {
        //muutujad ehk properties
        private $secret = 7;
        public $non_secret = 3;
        private $received_secret;

        // funktsioonid ehk methods
        function __construct($received) {
            echo "Klass on laetud! Konstruktor töötab!";
            $received_secret = $received;
            echo "Saabunud salajane number on: " .$this->received_secret ." ";
            $this->multiply();
        }

        function __destruct() {
            echo "Klass lõpetas!";
        }

        public function reveal() {
            echo "täiesti salajane number on " .$this->secret .". ";
        }

        private function multiply() {
            echo " Korrutis on: " .$this->secret * $this->non_secret * $this->received_secret;
        }

    } // classi lõpp.