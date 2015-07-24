<?php

/*

Template for xml-excel files

$filename: name of current file
$range:    excel range of cells
$cols, $rows ... what they mean

function caller example

function theme_render_template($file, $variables) {
  extract($variables, EXTR_SKIP);  // Extract the variables to a local namespace
  ob_start();                      // Start output buffering
  include "./$file";               // Include the file
  $contents = ob_get_contents();   // Get the contents of the buffer
  ob_end_clean();                  // End buffering and discard
  return $contents;                // Return the contents
}



*/
function excel_xml($filename,$result,$header) {
echo <<<END
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <LastAuthor>dantoni.massimo</LastAuthor>
  <LastPrinted>2008-09-15T11:02:32Z</LastPrinted>
  <Created>2008-09-15T09:06:55Z</Created>
  <LastSaved>2008-10-06T09:39:17Z</LastSaved>
  <Version>11.5606</Version>
 </DocumentProperties>
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <Colors>
   <Color>
    <Index>39</Index>
    <RGB>#E3E3E3</RGB>
   </Color>
  </Colors>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>12705</WindowHeight>
  <WindowWidth>19035</WindowWidth>
  <WindowTopX>0</WindowTopX>
  <WindowTopY>75</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s21">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="s22">
   <Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s23">
   <NumberFormat ss:Format="&quot;�&quot;\ #,##0"/>
  </Style>
  <Style ss:ID="s24">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
  </Style>
  <Style ss:ID="s25">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:Family="Swiss" ss:Size="14" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s26">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s27">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s28">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="&quot;�&quot;\ #,##0"/>
  </Style>
  <Style ss:ID="s29">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s30">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s31">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s32">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="&quot;�&quot;\ #,##0"/>
  </Style>
  <Style ss:ID="s33">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Interior ss:Color="#C0C0C0" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s34">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s35">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:Family="Swiss" ss:Bold="1"/>
   <NumberFormat ss:Format="&quot;�&quot;\ #,##0"/>
  </Style>
 </Styles>
END;
 echo  '<Worksheet ss:Name="'.substr($filename,0,31).'">';


    echo print_rows($filename,$result,$header);

    echo ' </Worksheet>
	    </Workbook>';


}

/*print core table and page breaks*/

function print_rows($filename,$result,$header){
	$row_count=0;
	$row_breaks='<RowBreaks>';
	$rows=0;
	$ward='';
	$sum=0;
	$first=TRUE;
	$content='';
    while ($links = db_fetch_object($result)) {
    	if ($links->struct<>$ward) {
    		if (!$first) {
				$content.='<Row>
				<Cell ss:StyleID="s26"><Data ss:Type="String"> </Data><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s26"><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s27"><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s26"><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s26"><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s35" ss:Formula="=SUM(R[-';
				$content.=$row_count.']C:R[-1]C)"><Data ss:Type="Number">'.$sum.'</Data><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s29"><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s27"><NamedCell ss:Name="Print_Area"/></Cell>
				<Cell ss:StyleID="s27"><NamedCell ss:Name="Print_Area"/></Cell>
				</Row>';
				$rows++;
				$row_count++;
			}
		 	else $first=FALSE; //end if (!$first)
			$row_breaks.='<RowBreak>
								<Row>'.$rows.'</Row>
								<ColEnd>9</ColEnd>
							</RowBreak>';
			$row_count=0;
			$sum=0;
			$ward=$links->struct;
//<!-- Set Title page (soc) -->
		$content.='<Row ss:Height="18">
		<Cell ss:StyleID="s25"><Data ss:Type="String">';
		$content.=$ward;
		$content.='</Data><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s26"><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s27"><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s26"><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s26"><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s28"><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s29"><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s27"><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s27"><NamedCell ss:Name="Print_Area"/></Cell>
		</Row>';
 	    $rows++;
 	    $row_count++;
//<!-- Table headers  -->
		$content.='<Row>
		<Cell ss:StyleID="s30"><Data ss:Type="String"> ID</Data><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s30"><Data ss:Type="String">PRI</Data><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s31"><Data ss:Type="String">DESCRIZIONE ATTREZZATURA</Data><NamedCell	ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s30"><Data ss:Type="String">NUM.</Data><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s30"><Data ss:Type="String">ANNO ACQ.</Data><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s32"><Data ss:Type="String">VALORE SENZA IVA</Data><NamedCell	ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s33"><Data ss:Type="String">TIPO</Data><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s31"><Data ss:Type="String">STATO</Data><NamedCell ss:Name="Print_Area"/></Cell>
		<Cell ss:StyleID="s31"><Data ss:Type="String">NOTE</Data><NamedCell ss:Name="Print_Area"/></Cell>
		</Row>';
		$rows++;
		$row_count++;

	} //end if ($links->ward<>$ward)

//<!-- Table rows  -->
	$content.='<Row>';
//<!-- "ID" -->
	$content.='<Cell ss:StyleID="s26"><Data ss:Type="String"> Rif:'.$links->nid .'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--PRI-->
	$content.='<Cell ss:StyleID="s26"><Data ss:Type="Number">'.$links->priority .'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--CAT	$row.='"'.$links->cud.'-->
//<!--DESCRIZIONE ATTREZZATURA-->
	$content.='<Cell ss:StyleID="s27"><Data ss:Type="String">'.check_plain($links->title) .'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--NUM.-->
	$content.='<Cell ss:StyleID="s26"><Data ss:Type="Number">'.$links->qta .'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--Anno di riferimento-->
	$content.='<Cell ss:StyleID="s26"><Data ss:Type="String">'.$links->year.'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--Valore SENZA IVA-->
	$content.='<Cell ss:StyleID="s28"><Data ss:Type="Number">'.$links->value.'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--TIPO-->
	switch ($links->type_acq){
						case 0: $s='I'; break;
						case 1: $s='S';break;
						case 2: $s='626';break;
				}
	$content.='<Cell ss:StyleID="s29"><Data ss:Type="String">'.$s.'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--Planning-->
	$content.='<Cell ss:StyleID="s27"><Data ss:Type="String">'.request_status_message($links->rcq_status).'</Data><NamedCell ss:Name="Print_Area"/></Cell>';
//<!--NOTE-->
	$content.='<Cell ss:StyleID="s27"><Data ss:Type="String">'.check_plain($links->note).'</Data><NamedCell ss:Name="Print_Area"/></Cell>
	</Row>';
		$rows++;
		$row_count++;
		$sum+=$links->value;
    } /*endwhile*/
    $content='<Names>
	   <NamedRange ss:Name="Print_Area" ss:RefersTo="=\''.$filename.'\'!R1C1:R'.$rows.'C9"/>
	   </Names>
	   <Table ss:ExpandedColumnCount="9" ss:ExpandedRowCount="'.$rows.'" x:FullColumns="1"  x:FullRows="1">
	   <Column ss:Index="2" ss:AutoFitWidth="0" ss:Width="27.75"/>
	   		<Column ss:StyleID="s21" ss:AutoFitWidth="0" ss:Width="286.5"/>
	   		<Column ss:AutoFitWidth="0" ss:Width="48.75" ss:Span="1"/>
	   		<Column ss:Index="6" ss:StyleID="s23" ss:AutoFitWidth="0" ss:Width="64.5"/>
	   		<Column ss:StyleID="s24" ss:AutoFitWidth="0"/>
		<Column ss:StyleID="s21" ss:AutoFitWidth="0" ss:Width="174.75"/>'.
	   $content.
	   '</Table>'.
	   pagebreaks($rows,9,$header).
	   $row_breaks. '</RowBreaks> </PageBreaks>';

    return $content;

} //endfunction

function pagebreaks($rows,$cols,$header){

return
  '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
    <PageSetup>
    <Header x:Data="'.$header.'"/>
    <Footer x:Data="&amp;R&amp;D"/>

     <Layout x:Orientation="Landscape"/>
     <PageMargins x:Bottom="0.984251969" x:Left="0.78740157499999996"
      x:Right="0.78740157499999996" x:Top="0.984251969"/>
    </PageSetup>
    <Print>
     <ValidPrinterInfo/>
     <PaperSizeIndex>9</PaperSizeIndex>
     <Scale>92</Scale>
     <HorizontalResolution>600</HorizontalResolution>
     <VerticalResolution>600</VerticalResolution>
    </Print>
    <ShowPageBreakZoom/>
    <PageBreakZoom>100</PageBreakZoom>
    <Selected/>
    <TopRowVisible>1</TopRowVisible>
    <Panes>
     <Pane>
      <Number>3</Number>
      <ActiveRow>1</ActiveRow>
      <ActiveCol>1</ActiveCol>
     </Pane>
    </Panes>
    <ProtectObjects>False</ProtectObjects>
    <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
  <PageBreaks xmlns="urn:schemas-microsoft-com:office:excel">
     <ColBreaks>
      <ColBreak>
       <Column>'.$cols.'</Column>
       <RowEnd>'.$rows.'</RowEnd>
      </ColBreak>
   </ColBreaks>';
 }