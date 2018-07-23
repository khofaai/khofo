<?php

namespace Khofo\vendor\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    /**
     * Simple validation
     *
     * @param type $data
     * @return boolean
     */
    public function simple_validation($data = [])
    {
        $validation = \Validator::make(request()->all(), $data);

        if ($validation->fails()) {

            return array(
                'errors' => $validation->errors()->toArray()
            );
        }
        return false;
    }
}
