<?php
/**
 * User: Jafar Jabr
 * Date: 12/15/2017
 * Feel Free to edit this Class
 */

require('fpdf.php');

class fpdfExtended extends FPDF
{
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';
    protected $title;
    protected $logo;
    protected $home_page;

    public function __construct(
        string $orientation = 'P',
        string $unit = 'mm',
        string $size = 'A4',
        string $title = "",
        string $logo = "",
        string $home_page = ""
    )
    {
        $orientation = strlen(trim($orientation)) > 0 ? $orientation : "P";
        $unit = strlen(trim($unit)) > 0 ? $unit : "mm";
        $size = strlen(trim($size)) > 0 ? $size : "A4";
        parent::__construct($orientation, $unit, $size);
        $this->title = $title;
        $this->logo = $logo;
        $this->home_page;
    }

    public function writeHTML($html)
    {
        // HTML parser
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Text
                if ($this->HREF) {
                    $this->PutLink($this->HREF, $e);
                } else {
                    $this->Write(5, $e);
                }
            } else {
                // Tag
                if ($e[0] == '/') {
                    $this->CloseTag(strtoupper(substr($e, 1)));
                } else {
                    // Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
                            $attr[strtoupper($a3[1])] = $a3[2];
                        }
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    public function imageInLine($image, $w, $h, $x, $text, $font_size)
    {
        $y = $this->GetY();
        $this->image($image, $x, $y, $w, $h);
        $this->SetFont("", "", $font_size);
        $text_x = $x + $w + 2;
        $this->setY($y, false);
        $this->setX($text_x);
        $this->Cell($w, $h, $text);
        $this->SetXY($x, $y + 2 * $h);
    }

    public function openTag($tag, $attr)
    {
        // Opening tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, true);
        }
        if ($tag == 'A') {
            $this->HREF = $attr['HREF'];
        }
        if ($tag == 'BR') {
            $this->Ln(5);
        }
    }

    public function closeTag($tag)
    {
        // Closing tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, false);
        }
        if ($tag == 'A') {
            $this->HREF = '';
        }
    }

    public function setStyle($tag, $enable)
    {
        // Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0) {
                $style .= $s;
            }
        }
        $this->SetFont('', $style);
    }

    public function putLink($URL, $txt)
    {
        // Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    public function header()
    {
        $this->Image($this->logo, 10, 6, 30, null, "", $this->home_page);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $w = $this->GetStringWidth($this->title) + 6;
        $this->SetX((210 - $w) / 2);
        // Colors of frame, background and text
        $this->SetDrawColor(0, 80, 180);
        $this->SetFillColor(230, 230, 0);
        $this->SetTextColor(220, 50, 50);
        // Thickness of frame (1 mm)
        $this->SetLineWidth(1);
        // Title
        $this->Cell($w, 9, $this->title, 1, 1, 'C', true);
        // Line break
        $this->Ln(30);
    }

    public function footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Text color in gray
        $this->SetTextColor(128);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}
