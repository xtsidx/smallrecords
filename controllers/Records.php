<?php namespace JanVince\SmallRecords\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Lang;
use JanVince\SmallRecords\Models\Record;
use JanVince\SmallRecords\Models\Settings;
use JanVince\SmallRecords\Models\Area;
use Redirect;
use Backend;
use Request;

class Records extends Controller
{

    protected $area_id;
    protected $areaName;
    protected $area;

    public $implement = [
    'Backend\Behaviors\ListController',
    'Backend\Behaviors\FormController',
    'Backend.Behaviors.RelationController',
    ];

    public $listConfig = [
        'default' => 'config_list.yaml'
    ];

    public $reorderConfig = 'config_reorder.yaml';

    public $formConfig = 'config_form.yaml';

    public $relationConfig = 'config_relation.yaml';

    public $nextRecord;
    public $previousRecord;

    public function __construct() {

        parent::__construct();

        BackendMenu::setContext('JanVince.SmallRecords', 'smallrecords', 'records' );

    }

    public function index($area_id) {

        if ( !$this->user->hasAccess([('janvince.smallrecords.access_area_'.$area_id)]) ) {
            \Flash::error( e(trans('janvince.smallrecords::lang.permissions.access_denied')) );
            return Redirect::to(Backend::url('/'));
        }

        $this->area_id = $area_id;

        BackendMenu::setContext('JanVince.SmallRecords', 'smallrecords', ('rec' . $this->area_id) );

        $area = Area::find($area_id);
        if ($area) {
            $this->areaName = $area->name;
            $this->pageTitle = $area->name;
        }

        $this->asExtension('ListController')->index();

    }

    public function create($area_id) {

        parent::create($area_id);

        if ( !$this->user->hasAccess([('janvince.smallrecords.access_area_'.$area_id)]) ) {
            \Flash::error( e(trans('janvince.smallrecords::lang.permissions.access_denied')) );
            return Redirect::to(Backend::url('/'));
        }

        $this->area_id = $area_id;

        $area = Area::find($area_id);
        if ($area) {
            $this->areaName = $area->name;
        }

        BackendMenu::setContext('JanVince.SmallRecords', 'smallrecords', ('rec' . $area_id) );

    }

    public function update($id, $area_id) {

        parent::update($id, $area_id);

        if ( !$this->user->hasAccess([('janvince.smallrecords.access_area_'.$area_id)]) ) {
            \Flash::error( e(trans('janvince.smallrecords::lang.permissions.access_denied')) );
            return Redirect::to(Backend::url('/'));
        }

        $this->area_id = $area_id;

        BackendMenu::setContext('JanVince.SmallRecords', 'smallrecords', ('rec' . $area_id) );

        $area = Area::find($area_id);
        if ($area) {
            $this->areaName = $area->name;
            $this->area = $area;
        }

        $record = Record::find($id);

        if($record ) {
            $this->vars['record'] = $record;
        }

        if($record ) {
            $this->nextRecord = $record->getNextRecordByDate();
        }

        if($record) {
            $this->previousRecord = $record->getPreviousRecordByDate();
        }


    }

    public function onDeleteAttachedImages($recordId, $context = ''){

        $record = Record::where('id', $recordId)->first();

        return $record->deleteAttachedImages();

    }

    public function getRecord($recordId) {

        return Record::where('id', $recordId)->first();

    }

    public function listExtendQuery($query)
    {

        // Filter by area
        if ($this->area_id) {
            $query->where('area_id', $this->area_id);
        }

    }

    public function getRecordsStats($part){

        switch($part){

            case 'records_count':
                return Record::where('area_id', $this->area_id)->count();
            break;

            case 'records_active':
                return Record::where('area_id', $this->area_id)->where('active', 1)->count();
            break;

            case 'records_hidden':
                return Record::where('area_id', $this->area_id)->where('active', 0)->count();
            break;

            case 'records_favourite':
                return Record::where('area_id', $this->area_id)->where('favourite', 1)->count();
            break;

            case 'records_common':
                return Record::where('area_id', $this->area_id)->where('favourite', 0)->count();
            break;

            case 'latest_records_name':
                return \Db::table('janvince_smallrecords_records')
                        ->where('area_id', $this->area_id)
                        ->orderBy('date', 'desc')
                        ->value('name');
            break;

            case 'latest_records_date':
                $date = new \DateTime(\Db::table('janvince_smallrecords_records')
                    ->where('area_id', $this->area_id)
                    ->orderBy('date', 'desc')
                    ->value('date'));
                return is_object($date) ? $date->format('j.n.Y') : $date;
            break;

            case 'active_area_name':
                $area = Area::find($this->area_id);
                if($area) {
                    return $area->name;
                }
            break;

            default:
                return NULL;
                break;

        }

    }

}
