<?php
 include(dirname(__DIR__).'/helpers/PrintHelper.php');
 class Automation {

    public $app_name = 'Description Automation PHP';
    public $output_directory_path;
    public $print_helper;
    public $templates;
    public $products;
    public $word_bank;
    public $manufacturers;

    function __construct($output_directory_path) {
        $this->print_helper = new PrintHelper($this->app_name);
        $this->output_directory_path = $output_directory_path;
    }
    private function check_output_folder_exists() {
        if (is_dir($this->output_directory_path)) {
          $this->print_helper->print_status('Directory "data" exists');
        }else {
          mkdir($this->output_directory_path);
          $this->print_helper->print_status('Directory "data" created');
        }
      }
    private function fetch_data_from_csv($filename) {
        $filesize = filesize($filename);
        $file = fopen($filename, 'r');
        $fetch_data = array();
        while (($line = fgetcsv($file,$filesize,";")) !== FALSE) {
            $fetch_data[] = $line[0];
        }
        return $fetch_data;
    }
    private function fetch_templates() {
        $templates_file = getcwd().'/data/templates.csv';
        $templates = $this->fetch_data_from_csv($templates_file);
        return $templates;
    }
    private function fetch_manufacturers() {
        $manufacturer_file = getcwd().'/data/manufacturer.csv';
        $manufacturers = $this->fetch_data_from_csv($manufacturer_file);
        return $manufacturers;
    }
    private function fetch_products() {
        $products_file  = getcwd().'/data/products.csv';
        $filesize = filesize($products_file);
        $file = fopen($products_file, 'r');
        $fetch_data = array();
        while (($line = fgetcsv($file,$filesize,",")) !== FALSE) {
            $fetch_data[] = $line[0];
        }
        return $fetch_data;
    }
    private function fetch_word_bank() {
        $word_bank_file = getcwd().'/data/word_bank.csv';
        $filesize = filesize($word_bank_file);
        $file = fopen($word_bank_file,'r');
        $word_bank = array();
        while (($line = fgetcsv($file, $filesize, ",")) !== false) {
            for($i=0;$i<sizeof($line);$i++) {
                $word_bank[$i][] = $line[$i];
            }
        }
        $word_bank_filtered = array();
        for ($i=0; $i < sizeof($word_bank) ; $i++) {
            $index = 0;
            if(sizeof($word_bank[$i]) == 0) {
                $word_bank_filtered[$i] = array();
            } else {
                for ($j=0; $j < sizeof($word_bank[$i]) ; $j++) {
                    if (!empty($word_bank[$i][$j])) {
                        $word_bank_filtered[$i][$index] = $word_bank[$i][$j];
                        $index++;
                    }
                }
            }
        }
        return $word_bank_filtered;
    }
    public function initializer() {
        $this->print_helper->print_app_name();
        $this->print_helper->print_status_heading("INITIALIZATION");
        $this->check_output_folder_exists();
        $this->templates = $this->fetch_templates();
        $this->print_helper->print_status("Retrieving 'Templates'");
        $this->word_bank = $this->fetch_word_bank();
        $this->print_helper->print_status("Retrieving 'Word Bank'");
        $this->manufacturers = $this->fetch_manufacturers();
        $this->print_helper->print_status("Retrieving 'Manufacturers'");
        $this->products = $this->fetch_products();
        $this->print_helper->print_status("Retrieving 'Products'");
    }
    private function str_replace_first($from, $to, $content) {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $content, 1);
    }
    private function add_word_bank($manufacture_replaced) {
        $check_attributes_present = TRUE;
        while($check_attributes_present) {
            $counter = 0;
            for($i=0; $i<sizeof($this->word_bank); $i++) {
                if(!empty($this->word_bank[$i][0])) {
                    $attribute = "[".$this->word_bank[$i][0]."]";
                    $count = substr_count($manufacture_replaced,$attribute);
                    for($j=0; $j<$count;$j++) {
                        $counter++;
                        $randomNumber = mt_rand(1,sizeof($this->word_bank[$i])-1);
                        $manufacture_replaced =$this->str_replace_first($attribute,$this->word_bank[$i][$randomNumber],$manufacture_replaced);
                    }
                }
            }
            if($counter == 0) {
                $check_attributes_present = FALSE;
            }
        }
        return $manufacture_replaced;
    }
    public function replace_custom_data() {
        $file_name = trim("result").'.csv';
        $templates_size = sizeof($this->templates);
        for($i=0; $i<$templates_size; $i++) {
            $helper_text = 'Populating data for template '.($i+1);
            $this->print_helper->print_status_heading($helper_text);
            $random_template_index = mt_rand(0,sizeof($this->templates)-1);
            $choosed_template = $this->templates[$random_template_index];
            $this->templates[$random_template_index] = '';
            $this->templates = array_values(array_filter($this->templates));
            $manufacturers = $this->manufacturers;
            for($j=0; $j<sizeof($this->manufacturers); $j++) {
                $count = 0;
                $result_array = array();
                $random_manufacture_index = mt_rand(0,sizeof($manufacturers)-1);
                $manufacture_replaced = str_replace('[Manufacturer]',$manufacturers[$random_manufacture_index],$choosed_template);
                $this->print_helper->print_status_heading("Writing data for ".$manufacturers[$random_manufacture_index]);
                $manufacturers[$random_manufacture_index] = '';
                $manufacturers = array_values(array_filter($manufacturers));
                $products = $this->products;
                for ($k=0; $k < sizeof($this->products); $k++) {
                    $random_products_index = mt_rand(0,sizeof($products)-1);
                    $product_replaced = str_replace('[product]',$products[$random_products_index],$manufacture_replaced);
                    $products[$random_products_index] = '';
                    $products = array_values(array_filter($products));
                    $result_array[$count][0] = $this->add_word_bank($product_replaced);
                    $count++;
                }
                $file = fopen($this->output_directory_path.'/'.$file_name,"a");
                foreach ($result_array as $line) {
                    fputcsv($file, $line);
                }
            }
        }
        echo"\n";
        $this->print_helper->print_status('Writing data completed');
        echo"\n";
    }
 }
