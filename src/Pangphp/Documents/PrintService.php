<?php

namespace Pangphp\Documents;

class PrintService {
  
  protected $_document;
  protected $_config;

  function __construct(DocumentService $doc, $config) {
    $this->_document = $doc;
    $this->_config = $config;
  }

  function printHtmlToPdf($html) {

    // First we save the html to secure front end situation
    $tmp = $this->_document->createSecurePublicDir();
    $html_file = $tmp . '/' . 'printable.html';
    file_put_contents(getcwd() . $html_file, $html);

    // Then we generate the PDF
    $pdf_file = $tmp . '/' . 'printable.pdf';

    exec($this->_config->get('pdf_printer') . ' -d 300 -B 2 -L 0 -R 0 -T 2 --print-media-type http://' . $_SERVER["HTTP_HOST"] . $html_file . ' ' . getcwd() . $pdf_file);

    // Then we delete the HTML file
    unlink(getcwd() . $html_file);

    // Then the we return the path to the PDF file
    return $pdf_file;
    
  }

}