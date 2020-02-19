<?php

/**
 * @author Prima Noor 
 */
class RenderButton extends Database
{
    var $ModuleId = 0;

    function __construct($connectionNumber = 0)
    {
        $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/html/{dbe}/renderbutton.sql.php';
        parent::__construct($connectionNumber);
    }

    function Render()
    {
        $this->ModuleId = $this->GetModule($mod);
        $hakAkses = $this->GetGroupAksesModule($_SESSION['username'], $this->ModuleId);

        for ($i = 0; $i < sizeof($hakAkses); $i++) {
            $arrHakAkses[$i] = $hakAkses[$i]['AksiId'];
        }
        $return = $this->GetKodeAksi($arrHakAkses);
        return $return;
    }

    function GetModule($module)
    {
        if ($module == '') {
            $mod = (string )$_GET['mod'];
            $sub = (string )$_GET['sub'];
            $act = (string )$_GET['act'];
            $type = (string )$_GET['typ'];
        } else {
            $modul = explode("|", $module);
            $mod = $modul[0];
            $sub = $modul[1];
            $act = $modul[2];
            $type = $modul[3];
        }

        $appId = GTFWConfiguration::GetValue('application', 'application_id');

        $result = $this->GetModuleId($mod, $sub, $act, $type, $appId);
        return $result[0]['modId'];
    }

    function GetGroupAksesModule($userName, $moduleId)
    {
        $result = $this->Open($this->mSqlQueries['get_group_akses_module'], array($userName, $moduleId));
        return $result;
    }

    function GetKodeAksi($arrHakAkses)
    {
        if (!empty($arrHakAkses)) {
            $kodeId = implode("', '", $arrHakAkses);
            $result = $this->Open($this->mSqlQueries['get_kode_aksi'], array($kodeId));
        } else {
            $result = array();
        }

        return $result;
    }

    function GetModuleId($mod, $sub, $act, $typ, $app)
    {
        return $this->Open($this->mSqlQueries['get_module_id'], array($mod, $sub, $act, $typ, $app));
    }
}

?>