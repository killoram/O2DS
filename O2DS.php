<?php
    # PROJECT O2DS
    # Object Oriented Data Storage

    /*
      O2DS is a object oriented, flat file, data storage system.
      For every base type of data that your want to store
      (such as employees, posts, notes) you create a data UNIT.
      For each category or variation of your data, you create a
      data SLOT. The each slot points to its unit. Slots, not
      units have multiple functions for handling their data.
      Units have a couple functions for handling data but
      should often be left alone unless you want to make a
      strictly key/value pair based system (which is what a unit
       is). The O2DS project is completely open souce so knock
       yourself out!
    */

  /* LICENSE

  Copyright (c) 2015 Seth Vandebrooke

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.

  */
    function read_file($fname) {
        return file_get_contents($fname);
    }
    function write_file($fname,$text) {
        file_put_contents($fname, $text);
    }
    function SHA256($string) {
        return hash("sha256", $string);
    }

    class dataUnit {
        public function __construct($name) {
            $this -> dataFilePath = './data-units/'.SHA256($name).'.du';
            if (!file_exists('./data-units')) {
                mkdir('./data-units');
            }
            if (!file_exists($this->dataFilePath)) {
                write_file($this->dataFilePath, json_encode(array("created"=> date("F j, Y, g:i a"))));
            }
        }
        public function getRawData() {
            $fileName = $this->dataFilePath;
            $data = read_file($fileName);
            $decoded = (array)json_decode($data);
            return $decoded;
        }
        public function getDataFromFile($key) {
            $data = $this->getRawData();
            if(array_key_exists($key,$data)) {
                return $data[$key];
            } else {
                return FALSE;
            }
        }
        public function setDataToFile($key,$value) {
            $data = $this->getRawData();
            $data[$key] = $value;
            $file = $this->dataFilePath;
            write_file( $file, json_encode($data) );
        }
    }

    class dataSlot {
        public function __construct($unitName, $dataName) {
            $this->unit = $unitName;
            $this->name = SHA256($dataName);
            if ($this->unit->getDataFromFile(SHA256($dataName))==FALSE) {
                $base = array();
                $this->unit->setDataToFile(SHA256($dataName), $base);
            }
        }
        public function addObject($object){
            $thisUnit = $this->unit;
            $thisName = $this->name;
            $base = $this->unit->getDataFromFile($thisName); //grab data section
            array_push($base, $object); //add object
            $this->unit->setDataToFile($thisName, $base); //save changes
        }
        public function getObjectWhere($whereThis,$equalsThis){
            $thisUnit = $this->unit;
            $thisName = $this->name;
            $base = $this->unit->getDataFromFile($thisName); //grab data section
            for ($i=0; $i < count($base); $i++) { //loop through data
                $obj = (array)$base[$i];
                if ($obj[$whereThis]==$equalsThis) {
                    return $base[$i];
                }
            }
            return FALSE;
        }
        public function editObject($whereThis,$equalsThis,$setThis,$toThis){
            $thisUnit = $this->unit;
            $thisName = $this->name;
            $base = $this->unit->getDataFromFile($thisName); //grab data section
            for ($i=0; $i < count($base); $i++) { //loop through data
                $obj = (array) $base[$i]; //get current data object
                if ($obj[$whereThis]==$equalsThis) { //if the object matches the parameters
                    $obj[$setThis] = $toThis; //edit the object
                    $base[$i] = (object) $obj; //update the base of data
                    $this->unit->setDataToFile($thisName, $base); //save the changes
                }
            }
        }
        public function removeObject($whereThis,$equalsThis){
            $thisUnit = $this->unit;
            $thisName = $this->name;
            $base = $this->unit->getDataFromFile($thisName); //grab data section
            for ($i=0; $i < count($base); $i++) { //loop through data
                $obj = (array) $base[$i]; //get current data object
                if ($obj[$whereThis]==$equalsThis) { //if the object matches the parameters
                    array_splice($base, $i, 1); //remove the object
                    $this->unit->setDataToFile($thisName, $base); //save the changes
                }
            }
        }
        public function filterObjects($listOfKeysAndValues){
            $thisUnit = $this->unit;
            $thisName = $this->name;
            $output = array();
            $base = $this->unit->getDataFromFile($thisName); //grab data section
            for ($i=0; $i < count($base); $i++) { //loop through data
                $obj = (array) $base[$i]; //get current data object
                foreach ($listOfKeysAndValues as $key => $value) { //check if all of the keys and values match
                    if ($obj[$key]==$value) {
                        $check = true;
                        continue;
                    } else {
                        $check = false;
                        break;
                    }
                }
                if ($check==true) { //if they all check out then add the object
                    array_push($output, $base[$i]);
                }
            }
            if (count($output)>0) { //return the list of objects
                return $output;
            } else {
                return FALSE; //unless if there aren't any
            }
        }
        public function listAllObjects(){
            $thisUnit = $this->unit;
            $thisName = $this->name;
            $base = $this->unit->getDataFromFile($thisName); //get list of all data objects
            return $base; //retrun list of objects
        }
    }
    #echo 'No errors here :)';
    /*
    $database = new dataUnit('mydatabase');
    $table = new dataSlot($database, 'users');
    $table->addObject( (object)array("name" => "John", "password" => "blablabla") );
    $pass = $table->getObjectWhere("name","John")->password;
    */
?>
