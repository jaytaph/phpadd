<?php

/**
 * phpadd - abandoned docblocks detector
 * Copyright (C) 2010 Francesco Montefoschi <francesco.monte@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package phpadd
 * @author  Francesco Montefoschi
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL 3.0
 */

class PHPADD_Stats
{
	/**
	 * Compute stats from mess.
	 * 
	 * @param PHPADD_Result_Analysis $mess
	 * @return array Contains files, methods, regular(-f), missing(-f) and outdated(-f) indexes.
	 */
	public function getStats(PHPADD_Result_Analysis $mess)
	{
		$fileNo = $mess->getCount();

		list ($total, $regular, $missing, $outdated, $nodocblock, $unordered) = $this->analyze($mess);
		$regularFreq = $this->getFrequency($regular, $total);
		$missingFreq = $this->getFrequency($missing, $total);
		$outdatedFreq = $this->getFrequency($outdated, $total);
		$nodocblockFreq = $this->getFrequency($nodocblock, $total);
		$unorderedFreq = $this->getFrequency($unordered, $total);

		return array(
			'files' => $fileNo,
			'methods' => $total,

			'regular' => $regular,
			'regular-f' => $regularFreq,

			'nodocblock' => $nodocblock,
			'nodocblock-f' => $nodocblockFreq,

			'missing' => $missing,
			'missing-f' => $missingFreq,

			'outdated' => $outdated,
			'outdated-f' => $outdatedFreq,

			'unordered' => $unordered,
			'unordered-f' => $unorderedFreq,
		);
	}

	/**
	 * Gets the percentage starting from absolute frequencies.
	 *
	 * @param int $partial
	 * @param int $total
	 * @return float
	 */
	private function getFrequency($partial, $total)
	{
		return number_format($partial / $total * 100, 1);
	}

	/**
	 * Detects the number of regular, missing and outdated docblocks.
	 *
	 * @param array $results
	 * @return array
	 */
	private function analyze(PHPADD_Result_Analysis $mess)
	{
		$regularMethods = 0;
		$missingMethods = 0;
		$outdatedMethods = 0;
		$nodocblockMethods = 0;
		$unorderedMethods = 0;

		foreach ($mess->getFiles() as $file) {
			foreach ($file->getClasses() as $class) {
				$regularMethods += $class->getRegularBlocks();
				$unorderedMethods += $class->getUnorderedBlocks();
				$missingMethods += count($class->getMissingBlocks());
				$outdatedMethods += count($class->getOutdatedBlocks());
				$nodocblockMethods += count($class->getNodocblockBlocks());
			}
		}

		$total = $class->methodCount();

		return array($total, $regularMethods, $missingMethods, $outdatedMethods, $nodocblockMethods, $unorderedMethods);
	}
}