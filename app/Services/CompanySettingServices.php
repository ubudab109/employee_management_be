<?php

namespace App\Services;

use App\Repositories\CompanySetting\CompanySettingInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class CompanySettingServices
{
    public $companySetting;

    /**
     * Constructor
     * @param CompanySettingInterface $companySetting
     */
    public function __construct(CompanySettingInterface $companySetting)
    {
        $this->companySetting = $companySetting;
    }

    /**
     * List company setting
     * 
     * @return array
     */
    public function list()
    {
        $data = $this->companySetting->listCompanySetting();
        return [
            'status'  => true,
            'message' => 'Data Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * Update company setting data
     * @param array $data - data to update with key and value
     * @return array
     * @throws \Exception
     */
    public function update(array $data)
    {
        DB::beginTransaction();
        try {
            
            foreach($data['data'] as $setting) {
                if ($setting['key'] == 'company_logo') {
                    $file = $setting['value'];
                    $imageName = storeImages('public/images/company-logo/', $file);
                    $setting['value'] = URL::to('storage/images/company-logo/'. $imageName);
                }
                $data = $this->companySetting->updateCompanySetting($setting['key'], $setting['value']);
                if (!$data) {
                    return [
                        'status'   => false,
                        'message'  => defaultResponseError(),
                    ];
                }
            }
            DB::commit();
            return [
                'status'   => true,
                'message'  => 'Data updated successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'  => false,
                'message' => defaultResponseError($err->getMessage())
            ];
        }
    }

    /**
     * List company setting
     * 
     * @return array
     */
    public function listSetting()
    {
        $data = $this->companySetting->listCompanySetting();
        return [
            'status'   => true,
            'message'  => 'Data fetched successfully',
            'data'     => $data
        ];
    }
}