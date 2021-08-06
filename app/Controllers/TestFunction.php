<?php

namespace App\Controllers;

use \Smalot\PdfParser\Parser;

class TestFunction extends BaseController
{
	public function index()
	{
        $parser = new Parser();

        $pdf    = $parser->parseFile('public/assets/uploads/filetest.pdf');
 
        $text = $pdf->getText();
        echo $text;
	}
}
