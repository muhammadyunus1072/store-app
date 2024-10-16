<?php

namespace App\Models\Logistic\Transaction\StockRequest;

use Carbon\Carbon;
use App\Traits\Document\HasApproval;
use App\Helpers\NumberGenerator;
use App\Models\Core\Setting\Setting;
use Illuminate\Support\Facades\Crypt;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Repositories\Core\Setting\SettingRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use App\Repositories\Document\Transaction\ApprovalHistoryRepository;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigRepository;
use App\Repositories\Document\Master\StatusApproval\StatusApprovalRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Purchasing\Transaction\PurchaseRequest\PurchaseRequestRepository;

class StockRequest extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasApproval;


    protected $fillable = [
        'warehouse_requester_id',
        'warehouse_requested_id',
        'request_date',
        'approved_date',
        'cancel_date',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(self::class, "SR");
            $model = $model->warehouseRequester->saveInfo($model, 'warehouse_requester');
            $model = $model->warehouseRequested->saveInfo($model, 'warehouse_requested');
        });

        self::created(function ($model) { 
            $setting = SettingRepository::findByName(Setting::NAME_LOGISTIC);

            $settings = json_decode($setting->setting, true);
            $approvalConfigs = ApprovalConfigRepository::getByKey($settings['approval_key_stock_request']);
            foreach($approvalConfigs as $approvalConfig)
            {
                $config = json_decode($approvalConfig->config, true);
                $is_match = ApprovalConfig::isMatch($model, $config);
                if($is_match)
                {
                    $data = [
                        'remarks_id' => $model->id,
                        'remarks_type' => self::class,
                        'config' => $approvalConfig->config,
                        'is_sequentially' => $approvalConfig->is_sequentially,
                    ];
        
                    $approval = ApprovalRepository::create($data);
                    $approvalId = $approval->id;
    
                    foreach($approvalConfig->approvalConfigUsers as $index => $approvalConfigUser)
                    {
                        $validatedData = [
                            'approval_id' => $approvalId,
                            'user_id' => $approvalConfigUser['user_id'],
                            'position' => $approvalConfigUser['position'],
                        ];
    
                        ApprovalUserRepository::create($validatedData);
                    }
                    return ;
                }
            }
        });

        self::updating(function ($model) {
            if ($model->getOriginal('warehouse_requester_id') != $model->warehouse_requester_id) {
                $model = $model->warehouseRequester->saveInfo($model, 'warehouse_requester');
            }
            if ($model->getOriginal('warehouse_requested_id') != $model->warehouse_requested_id) {
                $model = $model->warehouseRequested->saveInfo($model, 'warehouse_requested');
            }
        });

        self::deleted(function ($model) {
            $model->stockRequestProducts()->delete();
        });
    }

    public function onStatusSubmit($approvalHistory)
    {
        $approval = $this->approval()->first();
        
        // Approved When All Position 2 is Approve
        $approve_users = collect($approval->approvalUsers)->where('position', '=', 2);

        foreach($approve_users as $approve_user)
        {
            $status_approved = StatusApprovalRepository::findByName('Setuju');
            $history = ApprovalHistoryRepository::findByUser($this->id, $approve_user['user_id'], $status_approved->id);
            
            if(!$history)
            {
                return;
            }
        }

        StockRequestRepository::update($this->id, [
            'approved_date' => Carbon::now(),
            'cancel_date' => null,
        ]);
    }
    
    public function onStatusCancel($approvalHistory)
    {
        
        // Cancel When Position 1 is Cancel
        if($approvalHistory->position == 1)
        {
            StockRequestRepository::update($this->id, [
                'cancel_date' => Carbon::now(),
                'approved_date' => null,
            ]);
        }
    }

    public function viewShow($approvalUser)
    {
        $approval = $this->approval()->first();
        return [
            'component' => 'logistic.transaction.stock-request.show',
            'data' => [
                'approvalId' => Crypt::encrypt($approval->id),
                'userId' => Crypt::encrypt($approvalUser->user_id),
                'position' => $approvalUser->position,
            ],
        ];
    }
    
    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    public function warehouseRequester()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_requester_id', 'id');
    }

    public function warehouseRequested()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_requested_id', 'id');
    }

    public function stockRequestProducts()
    {
        return $this->hasMany(StockRequestProduct::class, 'stock_request_id', 'id');
    }
}
