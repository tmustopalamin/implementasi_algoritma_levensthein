<?php 

namespace App\Controllers;

use CodeIgniter\Controller;
use Smalot\PdfParser\Parser;
use Dompdf\Dompdf;
use App\Models\WordModel;
use CodeIgniter\HTTP\RequestInterface;
use App\Helpers\DocxToText;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;

ini_set('max_execution_time', -1); 
ini_set('memory_limit','2048M');

class Upload extends BaseController
{

    public function do_input_manual(){
        $request = \Config\Services::request();
        $textInput = $request->getVar('textInput');

        $array_text_mentah = $this->saring_dan_ubah_ke_array_dari_text_mentah($textInput);
        $words = $this->ambil_semua_kata_yang_ada_di_db();  
        $hasilnya = $this->konversi_text_yg_telah_di_koreksi_ke_satu_string($array_text_mentah, $words);
        $hasilnya = $this->hilangkan_tag_html($hasilnya);

        $data = [
            'title' => 'Hasil',
            'inputan'  => $textInput,
            'koreksi' => $hasilnya
        ];
    
        return json_encode($data);
        
    }

    public function do_upload(){
    	if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        //$request = \Config\Services::request();
        $file = $this->request->getFile('uploadedFile');
        $name = $file->getName();// Mengetahui Nama File
        $originalName = $file->getClientName();// Mengetahui Nama Asli
        $tempfile = $file->getTempName();// Mengetahui Nama TMP File name
        $ext = $file->getClientExtension();// Mengetahui extensi File
        $type = $file->getClientMimeType();// Mengetahui Mime File
        $size_kb = $file->getSize('kb'); // Mengetahui Ukuran File dalam kb
        $size_mb = $file->getSize('mb');// Mengetahui Ukuran File dalam mb

        
        //$namabaru = $file->getRandomName();//define nama fiel yang baru secara acak
        
        if ( ( $this->verifikasi_docx($type) || $this->verifikasi_pdf($type) ) ){	
            // File Tipe Sesuai        	
        	helper('filesystem'); // Load Helper File System
            helper('date');

        	$direktori = ROOTPATH.'uploads'; //definisikan direktori upload
        	$namabaru = now('Asia/Jakarta') . '_' . $originalName; //definisikan nama fiel yang baru
        	$map = directory_map($direktori, FALSE, TRUE); // List direktori

	        /* Cek File apakah ada */
	        foreach ($map as $key) {
	        	if ($key == $namabaru){
	        		delete_files($direktori,$namabaru); //Hapus terlebih dahulu jika file ada
	        	}
	        }
	        //Metode Upload Pilih salah satu
        	//$path = $this->request->getFile('uploadedFile')->store($direktori, $namabaru);
        	//$file->move($direktori, $namabaru)
	        if ($file->move($direktori, $namabaru)){
                //upload berhasil
	        	// return redirect()->to(base_url('uploadfile?msg=Upload Berhasil'));
                $path = $direktori .'\\'. $namabaru;

                $textDocument = "";
                $download_type = "";

                if($this->verifikasi_docx($type)){
                    //doc / docx 
                    $textDocument = $this->konversi_dari_docx_ke_text($path);
                    $download_type = "docx";
                }else{
                    //pdf
                    $textDocument = $this->konversi_dari_pdf_ke_text($path);
                    $download_type = "pdf";
                }

                $array_text_mentah = $this->saring_dan_ubah_ke_array_dari_text_mentah($textDocument);
                $words = $this->ambil_semua_kata_yang_ada_di_db();
                $hasilnya = $this->konversi_text_yg_telah_di_koreksi_ke_satu_string($array_text_mentah , $words);

                $data = [
                    'title' => 'Hasil',
                    'inputan'  => $textDocument,
                    'koreksi' => $hasilnya,
                    'download_type' => $download_type
                ];
            
                echo view('partials/header', $data);
                echo view('pages/hasil', $data);
                echo view('partials/footer', $data);
                
                delete_files($direktori);
                              
	        }else{
                echo 'Upload Gagal';
	        }
        }else{
        	// File Tipe Tidak Sesuai
            echo "format file salah" . $type;
        }
    }

    public function hasilkan_pdf(){
        $request = \Config\Services::request();
        $before = $request->getVar('before');
        $after = $request->getVar('after');

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml('<h3>Before</h3>' . $before . '<br><br><h3>After</h3>' . $after);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        helper('date');
        // Output the generated PDF to Browser
        $dompdf->stream(now('Asia/Jakarta'). '.pdf');
    }

    public function hasilkan_docx(){
        //ambil data dari form input
        $request = \Config\Services::request();
        $before = $request->getVar('before');
        $after = $request->getVar('after');

        $after = $this->hilangkan_tag_html($after);

        $phpWord = new PhpWord();

        $section = $phpWord->addSection();
        // Adding Text element to the Section having font styled by default...
        $section->addText(
            'Before'
        );

        $section->addText(
            $before
        );

        $section->addText(
            ""
        );

        $section->addText(
            'After'
        );

        $section->addText(
            $after
        );

        helper('date');
        $waktu = now('Asia/Jakarta');

        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save(ROOTPATH.'/public/assets/uploads/'. $waktu .'.docx');

        return redirect()->to('/assets/uploads/'. $waktu .'.docx');
    }

    private function hilangkan_tag_html($textnya){
        //hapus tag html jika ada di dalam teks, <br></br> akan dihapus
        $textnya = strip_tags($textnya, '<br>');
        $textnya = strip_tags($textnya, '</br>');
        return $textnya;
    }

    private function konversi_text_yg_telah_di_koreksi_ke_satu_string($array_text_mentah, $kumpulan_kata_dari_db){
        //konversi dari array ini -> array('budi','anak','yang','baik');
        //ke satu string seperti ini -> "budi anak yang baik";
        //dan string ini lah yg akan jadi output terakhir

        //siapkan string untuk memuat semua kata output
        $text_output = "";

        //looping $array_text_mentah (kumpulan kata dari inputan)
        //$satu_kata_dari_text_mentah (ini adalah 1 array yg diproses), looping akan terus berlanjut hingga element di array (kata yg ada di array) habis
        foreach($array_text_mentah as $satu_kata_dari_text_mentah){  
            //looping dan proses 1 per 1 lalu hasilnya masukkan ke variabel $text_output
            $text_output = $text_output . ' ';
            $text_output = $text_output . $this->proses_lev_dan_ubah_text($satu_kata_dari_text_mentah,$kumpulan_kata_dari_db);
        }
        return $text_output;
    }

    private function ambil_semua_kata_yang_ada_di_db(){
        //mengambil data dari wordlist dari database
        $db      = \Config\Database::connect();

        //pilih tabel typo_indonesia
        $builder = $db->table('typo_indonesia');

        //pilih kolom correct pada tabel
        $builder->select('correct');

        //ambil semua data 
        $words = $builder->get()->getResult();  

        //hasilnya adalah sebuah array kumpulan kata-kata yg ada di database
        return $words;
    }

    private function saring_dan_ubah_ke_array_dari_text_mentah($text_mentah){
        //ini fungsi untuk mengu

        $ubah_text_mentah_ke_huruf_kecil = strtolower($text_mentah);
        $ubah_text_mentah_ke_array = explode(" ", $ubah_text_mentah_ke_huruf_kecil);
        $text_array = array();
        foreach($ubah_text_mentah_ke_array as $text){                    

            //jika ada karakter ? <> ! ; : ' " , diabaikan
            if($this->abaikan_karakter_spesial($text) === true){
                continue;
            }

            //hilangkan karakter seperti simbol tanda tanya, dan karakter aneh lainnya
            $text = utf8_encode(str_replace("\t","",$text));

            //push/simpan text ke array
            array_push($text_array,trim($text));

        }

        return $text_array;
    }

    private function abaikan_karakter_spesial($text){
        if(!isset($text) || trim($text) === ''){
            return true;
        }

        if(strlen($text) < 2){
            return true;
        }

        if($text == "" ){
            return true;
        }

        if( empty($text) ){
            return true;
        }

        $pos1 = strpos($text, '.');
        if($pos1 !== false){
            return true;
        }

        $pos2 = strpos($text, ',');
        if($pos2 !== false){
            return true;
        }

        $pos3 = strpos($text, '|');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, '“');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, '[');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, ']');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, '<');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, '>');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, '/');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, '?');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, ';');
        if($pos3 !== false){
            return true;
        }

        $pos3 = strpos($text, ':');
        if($pos3 !== false){
            return true;
        }
    }

    private function proses_lev_dan_ubah_text($input,$words){           

        //inisiasi nomor jarak terpendek 
        $terpendek = -1;

        // loop through words to find the terdekat
        foreach ($words as $word) {
            $word = strtolower($word->correct);
            $nilai_lev = $this->algoritma_levenshtein($input, $word);

            if ($nilai_lev == 0) {
                $terdekat = $word;
                $terpendek = 0;
                break;
            }    

            if ($nilai_lev <= $terpendek || $terpendek < 0) {                
                $terdekat  = $word;
                $terpendek = $nilai_lev;
            }
        }        
        
        if ($terpendek == 0) {           
            return $terdekat;
        } else {
            return '<b>' . $terdekat . '</b>';
        }
    }

    private function algoritma_levenshtein($s1,$s2){

        $l1 = strlen($s1);
        $l2 = strlen($s2);
        $dis = range(0, $l2);

        for($x=1; $x<=$l1; $x++){
            $dis_new[0] = $x;

            for($y=1; $y<=$l2; $y++){
                $c = ($s1[$x-1] == $s2[$y-1]) ? 0 : 1;

                $dis_new[$y] = min(
                    $dis[$y] +1,
                    $dis_new[$y-1]+1,
                    $dis[$y-1]+$c
                );
            }
            $dis = $dis_new;
        }

        return $dis[$l2];

    }

    private function konversi_dari_pdf_ke_text($path){
        $parser = new Parser();
        $pdf    = $parser->parseFile($path);
        
        $text = $pdf->getText();
        return $text;
    }

    private function konversi_dari_docx_ke_text($path){
        $docObj = new DocxToText($path);
        $docText = $docObj->convertToText();
        return strtolower($docText);
    }   

    private function verifikasi_docx($type){
        //verifikasi apakah tipe filenya adalah docx
        return (($type ===('application/msword')) || ($type === ('application/vnd.openxmlformats-officedocument.wordprocessingml.document')));
    }

    private function verifikasi_pdf($type){
        //verifikasi apakah tipe filenya adalah pdf
        return ($type === ('application/pdf'));
    }
}