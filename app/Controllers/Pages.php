<?php 

namespace App\Controllers;

use CodeIgniter\Controller;

class Pages extends BaseController
{
    public function index()
    {
        $data['title'] = 'upload'; // Capitalize the first letter

        echo view('partials/header', $data);
        echo view('pages/upload', $data);
        echo view('partials/footer', $data);
    }

    public function view($page = 'upload')
    {
        if ( ! is_file(APPPATH.'/Views/pages/'.$page.'.php'))
        {
            // Whoops, we don't have a page for that!
            throw new \CodeIgniter\Exceptions\PageNotFoundException($page);
        }

        $data['title'] = ucfirst($page); // Capitalize the first letter

        echo view('partials/header', $data);
        echo view('pages/'.$page, $data);
        echo view('partials/footer', $data);
    }

    
}