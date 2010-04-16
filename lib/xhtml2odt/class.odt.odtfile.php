<?php
# vim: set expandtab tabstop=4 shiftwidth=4:
/**
 * xhtml2odt - XHTML to ODT XML transformation
 * 
 * This script can convert a wiki page to the OpenDocument Text (ODT) format,
 * standardized as ISO/IEC 26300:2006, and the native format of office suites
 * such as OpenOffice.org, KOffice, and others.
 * 
 * It uses a template ODT file which will be filled with the converted
 * content of the exported Wiki page.
 * 
 * Based on the work on {@link http://open.comsultia.com/docbook2odf/
 * docbook2odt}, by Roman Fordinal
 * 
 * @author Aurélien Bompard <aurelien@bompard.org>
 * @copyright Aurélien Bompard <aurelien@bompard.org> 2009-2010
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2+
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package xhtml2odt
 */


/**
 * Conversion failure
 */
class ODTException extends Exception {}


/**
 * Handling of an ODT file based on a template (another ODT file)
 *
 * The template ODT file is given to the constructor. Then, you must:
 * - set the XSLT parameters,
 * - call the {@link compile} method,
 * - use either the {@link saveToFile} method or the {@link
 *   exportAsAttachedFile} method, depending on whether you want to save the
 *   file on disk or to push the result to the browser.
 */
class ODTFile {
    protected $contentXhtml;
    protected $odtfile;
    protected $odtfilepath;
    protected $tmpfiles = array();
    protected $contentXml;
    protected $stylesXml;
    protected $autostyles = array();
    protected $styles = array();
    protected $fonts = array();
    protected $images = array();
    
    private $template;
    private $xslparams = array();
    
    private $options = array(
        'template_var' => null,
        'url' => 'localhost',
        'get_remote_images' => true,
        'clean_input' => true,
        'verbose' => true
    );
    
    const PIXEL_TO_CM = 0.026458333;

    /**
     * Constructor
     *
     * @param string $xhtml xhtml content
     * @param string $template the path to the template ODT file
     * @param array $options options
     **/
    public function __construct($xhtml, $template = null, $options = array()) {
        
        if (! class_exists('ZipArchive')) {
            throw new ODTException('Zip extension not loaded - check your php
                settings, PHP 5.2 minimum with zip and XSL extensions is
                required.'); ;
        }
        if (! class_exists('XSLTProcessor')) {
            throw new ODTException('XSL extension not loaded - check your php
                settings, PHP 5.2 minimum with zip and XSL extensions is
                required.'); ;
        }
        
        $this->options = array_merge($this->options, $options);
        $this->template = is_null($template) ? dirname(__FILE__).'/template.odt' : $template;
        //$this->template = realpath('.').'/modules/dbreport/odt/template.odt';
        $this->contentXhtml = $xhtml;
        
        // Loading content.xml and styles.xml from the template
        $this->odtfile = new ZipArchive();
        if ($this->odtfile->open($this->template) !== true) {
          throw new ODTException("Error while Opening the file '{$this->template}' -
                                  Check your odt file");
        }
        if (($this->contentXml = $this->odtfile->getFromName('content.xml')) === false) {
            throw new ODTException("Nothing to parse - check that the
                                    content.xml file is correctly formed");
        }
        if (($this->stylesXml = $this->odtfile->getFromName('styles.xml')) === false) {
          throw new ODTException("Nothing to parse - check that the
                                  styles.xml file is correctly formed");
        }
        
        $this->odtfile->close();
        // Use you app's cache directory here instead of null:
        $tmp = tempnam(null, md5(uniqid()));
        copy($this->template, $tmp);
        $this->odtfilepath = $tmp;
    }

    public function __destruct() {
        if (file_exists($this->odtfilepath)) {
            unlink($this->odtfilepath);
        }
        foreach ($this->tmpfiles as $tmp) {
            unlink($tmp);
        }
    }

    public function __toString() {
        return $this->contentXml;
    }

    /**
     * Main function which runs the other
     *
     * If your app has a templating engine, you may want to use the template
     * ODT file as one of you app's templates. You would then do the following
     * steps:
     * - run it here through your template engine, which would produce a mix
     *   of ODT XML and XHTML.
     * - pass the result to the {@link xhtml2odt} method, which would only
     *   convert the XHTML to ODT, and leave the ODT untouched
     * - the rest of the function is identical
     */
    public function compile() {
        $odt = $this->xhtml2odt($this->contentXhtml);
        $odt = str_replace('<'.'?xml version="1.0" encoding="utf-8"?'.'>', '', $odt);
        // You can do some debugging here if you want to.
        //print $html;
        //print $this->contentXml;
        //print $odt;
        //print "\n";
        //exit();
        
        // If you're using the ODT file as a template in a templating engine,
        // you can just set $this->contentXml to the output of xhtml2odt()
        // Here, we'll show how to replace a given string in the template, or
        // how to append text to the template.
        if ($this->options["template_var"] != '' &&
                strpos($this->contentXml, $this->options["template_var"]) !== false) {
            $this->contentXml = preg_replace(
                    "/<text:p[^>]*>".$this->options["r"]."<\/text:p>/",
                    $odt, $this->contentXml);
        } else {
            $this->contentXml = str_replace("</office:text>",
                    "$odt</office:text>", $this->contentXml);
        }
        // Add the missing styles (used in content.xml but not defined in
        // styles.xml or automatic styles
        $this->addStyles();
    }

    /**
     * Clean up the HTML we get in input
     *
     * Because the stylesheets will only accept well-formed (and if possible
     * valid) XHTML.
     *
     * If you have XHTML *and* ODT mixed up in input, because you used
     * the ODT file as a template in your templating engine, then you
     * *can't* run it through "tidy". Or else you'd have to use the
     * input-xml option, and it does strange things like removing the
     * white space after links. I didn't find a way around this.
     */
    public function cleanupInput($xhtml) {
        // add namespace if you used the ODT file as a template
        //$xhtml = str_replace("<office:document-content", '<office:document-content xmlns="http://www.w3.org/1999/xhtml"', $xhtml);

        /* Won't work if you have ODT XML *and* XHTML as input */
        if (extension_loaded('tidy')) {
            $tidy_config = array(
                    'output-xhtml' => true,
                    'add-xml-decl' => false,
                    'indent' => false,
                    'tidy-mark' => false,
                    //'input-encoding' => "latin1",
                    'output-encoding' => "utf8",
                    'doctype' => "auto",
                    'wrap' => 0,
                    'char-encoding' => "utf8",
                ); 
            $tidy = new tidy;
            $tidy->parseString($xhtml, $tidy_config, 'utf8');
            $tidy->cleanRepair();
            $xhtml = "$tidy";
        }

        // replace html codes with unicode
        // http://www.mail-archive.com/analog-help@lists.meer.net/msg03670.html
        //$xhtml = str_replace("&nbsp;","&#160;",$xhtml);
        $xhtml = html_entity_decode($xhtml, ENT_COMPAT, "UTF-8");

        return $xhtml;
    }

    /**
     * Convert from XHTML to ODT using the stylesheets
     *
     * @param string $xhtml XHTML to convert
     * @return string resulting ODT XML
     */
    public function xhtml2odt($xhtml) {
        
        if ($this->options['clean_input']) $xhtml = self::cleanupInput($xhtml);
        $xhtml = $this->handleImages($xhtml);
        // run the stylesheets
        $xsl = dirname(__FILE__)."/xsl";
        
        $xmldoc = new DOMDocument();
        $xmldoc->loadXML($xhtml); 
        
        $xsldoc = new DOMDocument();
        $xsldoc->load($xsl."/xhtml2odt.xsl");

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsldoc);
        foreach ($this->xslparams as $pkey=>$pval) {
            $proc->setParameter("", $pkey, $pval);
        }
        $output = $proc->transformToXML($xmldoc);
        
        if ($output === false) {
            throw new ODTException('XSLT transformation failed');
        }
        
        return $output;
    }

    /**
     * Handle images.
     *
     * Download and include them when possible. Local and remote images are
     * handled differently.
     *
     * @param string $xhtml XHTML to look for images in
     * @return string XHTML with normalized img tags
     */
    protected function handleImages($xhtml) {
        
        return $xhtml;
        
        // Turn false absolute URLs into relative ones. Useful for a webapp.
        $xhtml = preg_replace('#<img ([^>]*)src="http://'.$this->options['url'].'#',
                              '<img \1src="', $xhtml);
        /* Since we're a command-line script, there is no notion of a "local
           image". Our handleLocalImg function will just convert the source
           to absolute URLs. See the top of the function for an example of
           what you could do in a webapp (2 lines !)
         */
        $xhtml = preg_replace_callback('#<img [^>]*src="([^"]+)"[^>]*>#',
                                       array($this,"handleLocalImg"), $xhtml);
        if ($this->options['get_remote_images']) {
            $xhtml = preg_replace_callback(
                        '#<img [^>]*src="(https?://[^"]+)"[^>]*#',
                        array($this,"handleRemoteImg"), $xhtml);
        }
        return $xhtml;
    }

    /**
     * Handling of local images (on this server)
     *
     * Must be called as a regexp callback. Outsources all the hard work to
     * the {@link handleImg} method.
     *
     * This implementation downloads the files that come from the same domain
     * as the XHTML document cames from, but server-based export plugins can
     * just retrieve it from the local disk, using either the
     * <samp>DOCUMENT_ROOT</samp> or any appropriate method (depending on the
     * web application you're writing an export plugin for).
     *
     * @param array $matches regexp matches
     * @return string regexp replacement
     */
    protected function handleLocalImg($matches) {
        $src = $matches[1];
        /* Example for a webapp:
        $file = $_SERVER["DOCUMENT_ROOT"].$src;
        return $this->handleImg($file, $matches);
        What follows is more complicated because we're a command-line script:
        - if the image is really local, include it
        - else, turn it into an absolute URL which will be downloaded later
        */
        if (strpos($src, "://") !== false and
                strpos($src, "file://") === false) {
            // This is an absolute link, don't touch it
            if ($this->options['verbose']) {
                print "Local image: $src is an absolute link\n";
            }
            return $matches[0];
        }
        if (strpos($src, "file://") == 0) {
            $file = substr($src, 7);
        } elseif (strpos($src, "/") == 0) {
            $file = $src;
        } else {
            // relative link
            $file = $_SERVER["DOCUMENT_ROOT"].$src;
        }
        if (realpath($file) !== false) {
            if ($this->options['verbose']) {
                print "Local image: $src is actually local !\n";
            }
            return $this->handleImg(realpath($file), $matches);
        }
        if (!$this->options['url']) {
            // There's nothing we can do here
            if ($this->options['verbose']) {
                print "Local image: $src not local, can't download\n";
            }
            return $matches[0];
        }
        if (function_exists("http_build_url")) {
            $newsrc = http_build_url($this->options['url'], $src);
        } else {
            $newsrc = $this->options['url']."/".$src;
        }
        if ($this->options['verbose']) print "Local image: $src -> $newsrc\n";
        return str_replace($src, $newsrc, $matches[0]);
    }

    /*
     * Download remote images with cURL
     *
     * Must be called as a regexp callback. Outsources all the hard work to
     * the {@link handleImg} method.
     *
     * @param array $matches regexp matches
     * @return string regexp replacement
     */
    protected function handleRemoteImg($matches) {
        if (!function_exists("curl_init")) {
            return $matches[0]; // abort
        }
        $url = $matches[1];
        if ($this->options['verbose']) {
            print "Downloading image from: $url\n";
        }
        // Use you app's cache directory here instead of null:
        $tempfilename = tempnam(null,"xhtml2odt-");
        $this->tmpfiles []= $tempfilename;
        $tempfile = fopen($tempfilename,"w");
        if ($tempfile === false) {
            return $matches[0];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $tempfile);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $result = curl_exec($ch);
        if ($result === false) {
            return $matches[0];
        }
        curl_close($ch);
        fclose($tempfile);
        return $this->handleImg($tempfilename, $matches);
    }

    /**
     * Insertion of the image in the ODT file and the content.xml file
     *
     * @param string $file the path to the image
     * @param array $matches regexp matches
     * @return string regext replacement
     * @throws ODTException
     */
    protected function handleImg($file, $matches) {
        if (!is_readable($file)) {
            throw new ODTException("Image $file is not readable or does "
                                  ."not exist");
        }
        $width = 0;
        $height = 0;
        if (strpos($matches[0], 'width="') !== false
                and strpos($matches[0], 'height="') !== false) {
            // Size is specified in the HTML, keep it
            $width = preg_replace('/.*\s+width="(\d+)(px)?".*/', '\1',
                                  $matches[0]);
            $height = preg_replace('/.*\s+height="(\d+)(px)?".*/', '\1',
                                   $matches[0]);
        }
        // Remove any previous size specification
        $matches[0] = preg_replace('/\s+width="[^"]*"/', '', $matches[0]);
        $matches[0] = preg_replace('/\s+height="[^"]*"/', '', $matches[0]);
        if (!$width or !$height) {
            // Could not find or extract the wanted size, use the real size
            $size = @getimagesize($file);
            if ($size === false) {
                $size = array($this->xslparams["img_default_width"],
                              $this->xslparams["img_default_height"]);
            }
            list ($width, $height) = $size;
        }
        $width *= self::PIXEL_TO_CM;
        $height *= self::PIXEL_TO_CM;
        $this->images[$file] = basename($file);
        // Remove existing sizes and replace them with the calculated size
        return str_replace($matches[1],"Pictures/".basename($file).'" width="'.$width.'cm" height="'.$height.'cm', $matches[0]);
    }
        
    /**
     * Inserts the generated ODT XML code into the content.xml and styles.xml
     * files
     */
    protected function _parse() {
        // automatic styles
        if ($this->autostyles) {
            $autostyles = implode("\n",$this->autostyles);
            if (strpos($this->contentXml, '<office:automatic-styles/>') !== false) {
                $this->contentXml = str_replace('<office:automatic-styles/>',
                                        '<office:automatic-styles>'.$autostyles.'</office:automatic-styles>',
                                        $this->contentXml);
            } else {
                $this->contentXml = str_replace('</office:automatic-styles>',
                                        $autostyles.'</office:automatic-styles>', $this->contentXml);
            }
        }
        // regular styles
        if ($this->styles) {
            $styles = implode("\n",$this->styles);
            $this->stylesXml = str_replace('</office:styles>',
                                   $styles.'</office:styles>', $this->stylesXml);
        }
        // fonts
        if ($this->fonts) {
            $fonts = implode("\n",$this->fonts);
            $this->contentXml = str_replace('</office:font-face-decls>',
                                    $fonts.'</office:font-face-decls>', $this->contentXml);
        }
    }

    /**
     * Internal save
     *
     * @throws ODTException
     */
    protected function _save() {
        
        $this->odtfile->open($this->odtfilepath, ZIPARCHIVE::CREATE);
        $this->_parse();
        
        if (! $this->odtfile->addFromString('content.xml', $this->contentXml)) {
            throw new ODTException('Error during file export');
        }
        if (! $this->odtfile->addFromString('styles.xml', $this->stylesXml)) {
            throw new ODTException('Error during file export');
        }
        foreach ($this->images as $imageKey => $imageValue) {
            $this->odtfile->addFile($imageKey, 'Pictures/' . $imageValue);
        }
        $this->odtfile->close();
    }

    /**
     * Exports the file as an HTTP attachment.
     *
     * If you're a web app, you'll probably want this.
     *
     * @param string $name name of the file to download (optional)
     * @throws ODTException
     */
    public function exportAsAttachedFile($name="") {
        $this->_save();
        if (headers_sent($filename, $linenum)) {
            throw new ODTException("headers already sent ($filename at $linenum)");
        }
        if( $name == "" ) {
            $name = md5(uniqid()) . ".odt";
        }
        header('Content-type: application/vnd.oasis.opendocument.text');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        readfile($this->odtfilepath);
    }

    /**
     * Saves the file to the disk
     *
     * Mainly useful for the command-line app, see {@link
     * exportAsAttachedFile} to have the browser download the file.
     *
     * @param string $name path to the file on the disk
     * @throws ODTException
     */
    public function saveToFile($name="") {
        $this->_save();
        if( $name == "" ) {
            $name = md5(uniqid()) . ".odt";
        }
        copy($this->odtfilepath, $name);
    }

    /**
     * Adds all missing styles and fonts in the document
     */
    protected function addStyles() {
        $xsl = dirname(__FILE__)."/xsl";
        $contentxml = new DOMDocument();
        $contentxml->loadXML($this->contentXml); 
        $stylesxml = new DOMDocument();
        $stylesxml->loadXML($this->stylesXml); 
        $xsldoc = new DOMDocument();
        $xsldoc->load($xsl."/styles.xsl");
        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsldoc);
        $this->contentXml = $proc->transformToXML($contentxml);
        $this->stylesXml = $proc->transformToXML($stylesxml);
        if ($this->contentXml === false or $this->stylesXml === false) {
            throw new ODTException('Adding of styles failed');
        }
    }
    
    /**
     * Static function to generate odt content from xhtml in 1 call
     *
     * @param string $xhtml xhtml content
     * @param string $template the path to the template ODT file
     * @param array $options options
     */
    
    public static function get($xhtml, $template = null, $options = array())
    {
        $odf = new ODTFile($xhtml, $template, $options);
        $odf->compile();
        $odf->_save();
        readfile($odf->odtfilepath);
        unset($odf);
    }
}
?>
