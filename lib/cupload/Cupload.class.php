<?
class CUploadSentinel {
  var $_sId = null;
  var $lockfile     = '';
  var $total_size   = 0;
  var $complete     = false;
  var $received     = 0;
  var $current      = '';
  var $speed        = 0;
  var $percent      = 0;
  var $start_time   = 0;
  var $elapsed_time = 0;
  var $error        = '';
  
  function clear() {
       if(file_exists($this->lockfile)) {
            @unlink($this->lockfile);
       }
  }
  
  function __init($_sId) {
    $this->lockfile = UPLOAD_PATH.$_sId.'.lock';
    if(file_exists($this->lockfile)) {
      $pf=@fopen($this->lockfile,'rb');
      if (is_resource($pf))
      { 
        @flock($pf, LOCK_SH);
        $_status = fread($pf,4096);
        @flock($pf, LOCK_UN);
        fclose($pf);
        $_status = unserialize($_status);
        $this->total_size   = @ $_status['total_size'];
        $this->complete     = @ ($_status['complete']?"1":"0");
        $this->received     = @ $_status['received'];
        $this->current      = @ $_status['current'];
        $this->start_time   = @ $_status['start_time'];
        $this->elapsed_time = @ $_status['elapsed_time'];
        if (!empty($_status['files'])) $this->files        = @ $_status['files'];
        if($this->total_size>0)  $this->percent = sprintf('%5.2f',($this->received * 100) / $this->total_size);
        $_total_time = ($this->elapsed_time-$this->start_time);
        if($_total_time>0) {
          $this->speed = sprintf('%5.2f',(($this->received) / $_total_time)/1024);
        } else $this->speed=-1;
      }
      else $this->error = 'notfound';
    }
    else $this->error = 'notfound';
  }
}

class CUpload {
  var $sid          = null;
  var $total_size   = 0;
  var $complete     = false;
  var $current      = "";
  var $files        = null;
  var $postvars     = null;
  var $start_time   = 0;
  var $elapsed_time = 0;
  var $received     = 0;
  var $update       = 0;
  var $lockfile     = '';
  var $error        = '';

  function __init($_sId) {
    list($chunk,$boundary) = split("boundary=",$_SERVER['CONTENT_TYPE']);
    $this->boundary=trim($boundary);
    $this->sid          = $_sId;
    $this->postvars     = array();
    $this->total_size   = $_SERVER['CONTENT_LENGTH'];
    $this->complete     = false;
    $this->content_type = $_SERVER['CONTENT_TYPE'];
    $this->received     = 0;
    $this->lockfile     = UPLOAD_PATH.$_sId.'.lock';
    $this->start_time   = CUpload::getmicrotime();
    $this->append_progress();

  }
  function appendFile($fieldname,$filename,$filetype) {
    $tmpname = basename(tempnam(UPLOAD_PATH, "pkup"));
    $entry = array('name' => $fieldname,'filename' => $filename,'mime' => $filetype , 'tmpname' => $tmpname , 'size' =>0);
    if(!is_array($this->files)) $this->files = array();
    $this->files[] = $entry;
    $this->current = $filename;
    return $tmpname;
  }

  function processInput() {
    $putdata = fopen("php://stdin", "r");
    $current_file = "";
    if(!feof($putdata)) {
      while ($data = fgets($putdata, 8192)) {
        $this->received+=strlen($data);
        if(strlen($data)!=8192) {
          if(strstr($data,$this->boundary)) {
            unset($data);
            if(isset($_file)) fclose($_file);
            $_file = null;
            # get header
            $header = fgets($putdata,1024);  # header "content-disposition"
            $header .=fgets($putdata,1024);  # header "content-type"
            $header .=fgets($putdata,1024);  # CR/LF
            $this->received += strlen($header);

            $rc = preg_match_all("/Content-Disposition: form-data; name=\"([^\"]*)\"(; filename=\"([^\"]*)\"\r\nContent-Type: (\S+))?\r\n/i", $header, $matchesF, PREG_OFFSET_CAPTURE);
            if($rc) {

              $filename = trim($matchesF[3][0][0]);
              $filetype = trim($matchesF[4][0][0]);
              $fieldname = $matchesF[1][0][0];
              $filename = str_replace("\\","/",$filename);
              $filename = basename($filename);

              if(!empty($filename)) {
                if (is_dir(UPLOAD_PATH) && is_writable(UPLOAD_PATH)) 
                {
                    # create tmp file for upped file
                    $tmpname = $this->appendFile($fieldname,$filename,$filetype);
                    $_file = fopen(UPLOAD_PATH.$tmpname,'wb');
                }
                else $_file = null;
              }
              else
              {
                $rc = preg_match_all("/Content-Disposition: form-data; name=\"([^\"]*)\"\r\n\r\n(.*)\r\n/i", $header, $matchesF, PREG_OFFSET_CAPTURE);
                $fieldname = trim($matchesF[1][0][0]);
                $fieldvalue = trim($matchesF[2][0][0]);
                if (!empty($fieldname)) $this->postvars[$fieldname] = $fieldvalue;
              }
            }
          }
        }
        if($_file!=null) {
          if(isset($data)) fwrite($_file,$data);
        }
        $this->append_progress();
      }
      if(isset($_file)) fclose($_file);
    }
    fclose($putdata);
  }

  function setcomplete() {
    if (!empty($this->files))
        foreach($this->files as $_key => $_item) {
          $file = $_item['tmpname'];
          $filename=UPLOAD_PATH.$file;
          $fsize=filesize($filename);
          $pf=fopen($filename,'r+');
          fseek($pf,-2,SEEK_END);
          $eof = fread($pf,2);
          if($eof == "\x0d\x0a") {
            ftruncate($pf,$fsize-2);
            $fsize-=2;
          }
          fclose($pf);
          $this->files[$_key]['size'] = $fsize;
        }
    $this->complete=true;
    $this->update=0;
    $this->append_progress();
  }

  function check_complete() {
    if(($this->total_size != $this->received) || ($this->total_size=0)) {
      # file upload error.. remove all files.
      if(is_array($uploader->files)) {
        foreach($uploader->files as $_item) {
          @unlink(UPLOAD_PATH.$_sId.'_'.$_item['tmpname']);
        }
      }
      $uploader->received=0;
      $uploader->error = 0xff;
      @unlink(UPLOAD_PATH.$_sId.'.lock');
      return false;
    }
    return true;
  }


  function append_progress() {
    if(!$this->complete) {
      if($this->getmicrotime() - $this->update < 0.625) return;
      $this->update = $this->getmicrotime();
    }
    $this->elapsed_time = $this->getmicrotime();
    $_status = array(
      'total_size'   => $this->total_size,
      'complete'     => $this->complete,
      'received'     => $this->received,
      'current'      => $this->current,
      'start_time'   => $this->start_time,
      'elapsed_time' => $this->elapsed_time,
      'error'        => '',
    );
    if($this->complete) {
      # store file upload result.
      $_status['files'] = $this->files;
    }
    $_status = serialize($_status);

    if (is_dir(UPLOAD_PATH) && is_writable(UPLOAD_PATH))
    {
        $fp=fopen($this->lockfile,'wb');
      #  @flock($fp, LOCK_EX|LOCK_NB);
        fwrite($fp,$_status);
      #  @flock($fp, LOCK_UN);
        fclose($fp);
    }
    else $this->error = 'notwritable';
  }

  function getmicrotime() {
    list($usec, $sec) = explode(" ",microtime());
    $t0= ((float)$usec + (float)$sec);
    return $t0;
  }
}
?>
