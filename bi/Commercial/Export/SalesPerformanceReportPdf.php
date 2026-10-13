<?php

namespace SAHamid\BI\Commercial\Export;

/** Minimal branded TCPDF page furniture for generated report packs. */
class SalesPerformanceReportPdf extends \TCPDF
{
	public $reportRunKey = '';

	public function Header()
	{
		$this->SetFont('helvetica', 'B', 8);
		$this->SetTextColor(23, 63, 95);
		$this->Cell(0, 5, 'S.A. Hamid ERP  |  Sales Performance  |  PROVISIONAL REPORT', 0, 1, 'L');
		$this->SetDrawColor(31, 138, 112);
		$this->Line(11, 15, 286, 15);
	}

	public function Footer()
	{
		$this->SetFont('helvetica', '', 7);
		$this->SetTextColor(90, 100, 110);
		$this->SetY(-10);
		$this->Cell(220, 5, 'Frozen run ' . $this->reportRunKey . '  |  PKR values where known  |  Separate commercial stages', 0, 0, 'L');
		$this->Cell(55, 5, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
	}
}
