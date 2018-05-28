<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class Index extends Controller
{
    public function index()
    {
        return "Hello World";
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    public function test(Request $request)
    {

        if($request->isPost()){

            $file = $_FILES['file'];

            $data = excel_import($file);

            dump($data);

        }
        
        return $this->fetch();
    }
}
