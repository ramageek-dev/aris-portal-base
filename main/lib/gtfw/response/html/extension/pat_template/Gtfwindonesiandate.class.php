<?php
class patTemplate_Modifier_Gtfwindonesiandate extends patTemplate_Modifier {
   /**
   * modify the value
   *
   * @access   public
   * @param   string      value
   * @return   string      modified value
   */
   function modify($value, $params = array()) {
      if (!isset($value) || $value == NULL || $value == '')
         return '';

      $indonesian_months = array('N/A', 'Januari', 'Februari', 'Maret', 'April', 'Mei',
         'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
      $date_format = array('long' => '%d %s %04d', 'short' => '%02d<sep>%02d<sep>%04d');

      $default_date_format = 'long';
      $default_date_separator = '-';

      if(!isset($params['format']) || $params['format'] == NULL || $params['format'] == '' ||
         !array_key_exists(strtolower($params['format']), $date_format))
         $params['format'] = $default_date_format;
      // paranoia
      $params['format'] = strtolower($params['format']);

      if(!isset($params['separator']) || $params['separator'] == NULL || $params['separator'] == '')
         $params['separator'] = $default_date_separator;

      if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $value, $parts)) {
         $parts[1] = (int) $parts[1]; // year
         $parts[2] = (int) $parts[2]; // month
         $parts[2] = $params['format'] == 'long' ? $indonesian_months[$parts[2]] : $parts[2];
         $parts[3] = (int) $parts[3]; // date
         $parts[4] = (int) $parts[4]; // hours
         $parts[5] = (int) $parts[5]; // minutes
         $parts[6] = (int) $parts[6]; // seconds
         $result = sprintf($date_format[$params['format']] . ' %02d:%02d:%02d',
            $parts[3], $parts[2], $parts[1], $parts[4], $parts[5], $parts[6]);
         $result = str_replace('<sep>', $params['separator'], $result);
      } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $value, $parts)) {
         $parts[1] = (int) $parts[1]; // year
         $parts[2] = (int) $parts[2]; // month
         $parts[2] = $params['format'] == 'long' ? $indonesian_months[$parts[2]] : $parts[2];
         $parts[3] = (int) $parts[3]; // date
         $result = sprintf($date_format[$params['format']], $parts[3], $parts[2], $parts[1]);
         $result = str_replace('<sep>', $params['separator'], $result);
      } else {
         // assumed to be an integer (unix timestamp)
         $value = (int) $value;
         $parts = getdate($value);
         $parts['mon'] = $params['format'] == 'long' ? $indonesian_months[$parts['mon']] : $parts['mon'];
         $result = sprintf($date_format[$params['format']] . ' %02d:%02d:%02d',
            $parts['mday'], $parts['mon'], $parts['year'],
            $parts['hours'], $parts['minutes'], $parts['seconds']);
         $result = str_replace('<sep>', $params['separator'], $result);
      }

      return $result;
   }
}
?>
