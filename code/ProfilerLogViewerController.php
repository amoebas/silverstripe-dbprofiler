<?php
/**
 * Viewer for Profiler logs
 *
 * @package dbprofiler
 */
class ProfilerLogViewerController extends ContentController {

	/**
	 *
	 * @var array
	 */
	protected $rawdata = array();

	/**
	 * Init method for controller.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();
		include( getTempFolder( BASE_PATH . '-query-stats' ) . '/querystats.php' );
		$this->rawdata = $logData;
		foreach( $logData as $key => $data ) {
			if( is_array( $data ) ) {
				$logData[ $key ] = new ArrayList();
				foreach ($data as $i => $item) {
					$logData[ $key ][$i] = new ArrayData($item);
				}
			}
		}

		$this->dataRecord->castedUpdate( $logData );
	}

	/**
	 * Index action of controller.
	 *
	 * @param SS_HTTPRequest $request
	 *
	 * @return array
	 */
	public function index( SS_HTTPRequest $request ) {
		Requirements::javascript( 'https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js' );
		Requirements::javascript( 'dbprofiler/js/profiler.js' );
		Requirements::javascript( 'dbprofiler/js/jquery.tablesorter.min.js' );
		Requirements::css( 'dbprofiler/css/profiler.css' );
		return array();
	}

	/**
	 *
	 * @return string
	 */
	public function Link($action=null) {
		return Director::absoluteURL( 'ProfilerLogViewerController', true );
	}

	/**
	 *
	 * @param SS_HTTPRequest $request
	 * @return void
	 */
	public function Query( SS_HTTPRequest $request ) {
		$dowantKey = $request->param( 'ID' );
		$sql = $this->rawdata['Queries'][ $dowantKey ]['Query'];
		echo '<code>'.$sql.'</code>';
	}

	/**
	 *
	 * @param SS_HTTPRequest $request
	 * @return void
	 */
	public function Describe( SS_HTTPRequest $request ) {
		$dowantKey = $request->param( 'ID' );
		$sql = $this->rawdata['Queries'][ $dowantKey ]['Query'];
		if( !preg_match( "|^SELECT|", $sql ) ) {
			echo '<strong>Explain is not possible on this query</strong><br />';
			echo '<code>'.$sql.'</code>';
			return;
		}
		$data = DB::getConn()->query( 'DESCRIBE ' . $sql );
		echo '<table><thead><tr>';
		echo '<th class="center">Select type</td>';
		echo '<th>Table</td>';
		echo '<th>Type</td>';
		echo '<th>Indexes</td>';
		echo '<th>Chosen index</td>';
		echo '<th>Reference</td>';
		echo '<th>Rows</td>';
		echo '<th>Extra</td>';
		echo '</tr></thead>';
		foreach( $data as $row ) {
			echo '<tr>';
			echo '<td>'.$row['select_type'].'</td>';
			echo '<td>'.$row['table'].'</td>';
			echo '<td>'.$row['type'].'</td>';
			echo '<td>'.$row['possible_keys'].'</td>';
			echo '<td>'.$row['key'].'</td>';
			echo '<td>'.$row['ref'].'</td>';
			echo '<td>'.$row['rows'].'</td>';
			echo '<td>'.$row['Extra'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
		
	}

	public function Backtrace( SS_HTTPRequest $request ) {
		$dowantKey = $request->param( 'ID' );
		echo $this->rawdata['Queries'][ $dowantKey ]['Backtrace'];
	}

}
