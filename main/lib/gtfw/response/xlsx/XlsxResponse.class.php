<?php

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
        'main/lib/PHPExcel/PHPExcel.php';

/**
 * XlsxResponse extend dari PHPExcel
 *
 * @category   XlsxResponse
 * @package    XlsxResponse
 * @copyright  Copyright (c) 2011 Gamatechno
 * @author apriskiswandi
 */
class XlsxResponse implements ResponseIntf {

    /**
     * Object untuk PHPExcel
     *
     * @var Excel
     * @access public
     */
    var $Excel;

    /**
     * Excel
     *
     * @var XlsxResponse_filename
     * @access private
     */
    private $filename;

    /**
     * Excel
     *
     * @var XlsxResponse_writer
     * @access private
     */
    private $writer = "Excel5"; //

    /**
     * Inisialisasi, Membuat File xls {@link $Excel}
     */

    function XlsxResponse() {
        $this->Excel = new PHPExcel();
    }

    /**
     * Set Filename : Memberi nama file xls 
     *
     * @param $filename (String) namafile.xls atau namafile.xlsx
     * @access public
     */
    function SetFileName($filename='') {
        $this->filename = $filename;
    }

    /**
     * Set Writer
     *
     * @param $writer (String) Excel5 atau Excel2007
     * @access public
     */
    function SetWriter($writer) {
        $this->writer = $writer;
    }

    function GetFileName() {
        if (trim($this->filename) == '')
            $this->filename = "data_" . date('Ymdhis') . ".xlsx";
        return $this->filename;
    }
    
    function ProcessRequest() {
        echo 'XlsxResponse->ProcessRequest(): This function must be overrided!';
        return NULL;
    }

    function &GetHandler() {
        return $this;
    }

    /**
     * Save : Menimpan File xls 
     * 
     * @param $path (String) optional: path file
     * @access public
     */
    function Save($path="") {
        if (trim($path) != "") {
            try {
                $objWriter = PHPExcel_IOFactory::createWriter($this->Excel, $this->writer);
                $objWriter->save($path);
                return $path;
            } catch (Exception $exc) {
                die($exc->getMessage());
            }
        } else {
            try {
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $this->GetFileName() . '"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($this->Excel, $this->writer);
                $objWriter->save('php://output');
            } catch (Exception $exc) {
                die($exc->getMessage());
            }
        }
    }

    function Send() {
        $this->ProcessRequest();
    }

}

?>
