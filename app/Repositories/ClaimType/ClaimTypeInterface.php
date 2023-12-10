<?php

namespace App\Repositories\ClaimType;

interface ClaimTypeInterface
{
	/**
	 * LIST CLAIM TYPE FOR EACH BRANCH
	 * @param string $keyword - FOR SEARCHING BY NAME IN CLAIM TYPE
	 * @return array
	 */
	public function listClaimType($keyword);

	/**
	 * DETAIL CLAIM TYPE
	 * @param integer $claimTypeId
	 * @return object
	 */
	public function detailClaimType($claimTypeId);

	/**
	 * CREATE NEW CLAIM TYPE
	 * @param array $data
	 * @return object
	 */
	public function createClaimType(array $data);

	/**
	 * UPDATE CLAIM TYPE
	 * @param array $data - DATA TO UPDATE
	 * @param integer $claimTypeId - CLAIM TYPE ID
	 * @return bool
	 */
	public function updateClaimType(array $data, $claimTypeId);

	/**
	 * DELETE CLAIM TYPE
	 * @param integer $claimTypeId - CLAIM TYPE ID
	 * @return object
	 */
	public function deleteClaimType($claimTypeId);
}