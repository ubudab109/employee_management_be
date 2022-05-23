<?php

namespace App\Repositories\CompanyJobStatus;

interface CompanyJobStatusInterface
{
  public function getAllJobStatus($keyword);
  public function getPaginateJobStatus($keyword, $show);
  public function createJobStatus(array $data);
  public function updateJobStatus(array $data, $id);
  public function detailJobStatus($id);
  public function deleteJobStatus($id);

}