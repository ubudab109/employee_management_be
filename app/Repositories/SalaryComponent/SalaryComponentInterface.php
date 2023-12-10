<?php

namespace App\Repositories\SalaryComponent;

interface SalaryComponentInterface
{
  /**
   * LIST SALARY COMPONENT
   * @param string $keyword
   * @param string $type
   * @return array
   */
  public function listSalaryComponent($keyword, $type);
  
  /**
   * DETAIL SALARY COMPONENT
   * @param int $id
   * @return object
   */
  public function detail($id);

  /**
   * CREATE SALARY COMPONENT
   * @param array $data
   * @return object
   */
  public function createSalaryComponent(array $data);

  /**
   * UPDATE SALARY COMPONENT
   * @param array $data
   * @param integer $id
   * @return bool
   */
  public function updateSalaryComponent(array $data, $id);

}
