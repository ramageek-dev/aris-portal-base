<?php

/**
 * PT. Gamatechno Indonesia
 *
 * Description of PdfxResponse
 *
 * @author apriskiswandi
 */
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
        'main/lib/tcpdf/config/lang/eng.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
        'main/lib/tcpdf/tcpdf.php';

class PdfxResponse extends TCPDF implements ResponseIntf {

    /**
     * Pdf
     *
     * @var PdfxResponse_Pdf
     */
    var $Pdf;

    /**
     * Pdf
     *
     * @var PdfxResponse_filename
     */
    private $filename;

    /**
     * Pdf
     *
     * @var PdfxResponse_dest default "I"
     */
    private $dest = "I";

    function PdfxResponse($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
        $this->Pdf = new TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    /**
     * Set Filename
     *
     * @param $filename String) namafile.pdf
     */
    function SetFileName($filename='') {
        $this->filename = $filename;
    }

    function GetFileName() {
        if (trim($this->filename) == '')
            $this->filename = "data_" . date('Ymdhis') . ".pdf";
        return $this->filename;
    }

    function &GetHandler() {
        return $this;
    }

    function ProcessRequest() {
        echo 'PdfxResponse->ProcessRequest(): This function must be overrided!';
        return NULL;
    }

    /**
     * Set Dest Untuk Mode Output
     *
     * I: send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.
     * D: send to the browser and force a file download with the name given by name.
     * F: save to a local server file with the name given by name.
     * S: return the document as a string (name is ignored).
     * FI: equivalent to F + I option
     * FD: equivalent to F + D option
     * E: return the document as base64 mime multi-part email attachment (RFC 2045)

     * 
     * @param $dest (String) I, D, F, S, FI, FD, E
     */
    function SetDest($dest) {
        $this->dest = $dest;
    }

    function Save($path="") {
        if (trim($path != '')) {
            $this->Pdf->Output($path, "F");
        } else {
            $this->Pdf->Output($this->GetFileName(), $this->dest);
        }
    }

    function Send() {
        $this->ProcessRequest();
    }

}

?>
