<?php

namespace App\Models\Document\Master;

use Illuminate\Support\Facades\Log;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Master\ApprovalConfigUser;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigRepository;
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use App\Repositories\Document\Transaction\ApprovalUserStatusApprovalRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalConfig extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    // LOGIC CHOICE
    const LOGIC_AND = 'AND';
    const LOGIC_OR = 'OR';
    const LOGIC_CHOICE = [
        self::LOGIC_AND => self::LOGIC_AND,
        self::LOGIC_OR => self::LOGIC_OR,
    ];

    // TYPE CHOICE
    const TYPE_COLUMN = 'column';
    const TYPE_GROUPER = 'grouper';
    const TYPE_CHOICE = [
        self::TYPE_COLUMN => "Kolom",
        self::TYPE_GROUPER => "Grouper",
    ];

    // OPERATOR CHOICE
    const OPERATOR_ASSIGNMENT = '=';
    const OPERATOR_GREATER_THAN_OR_EQUAL = '>=';
    const OPERATOR_LESS_THAN_OR_EQUAL = '<=';
    const OPERATOR_GREATER_THAN = '>';
    const OPERATOR_LESS_THAN = '<';
    const OPERATOR_NOT_EQUAL = '!=';
    const OPERATOR_CHOICE = [
        self::OPERATOR_ASSIGNMENT => self::OPERATOR_ASSIGNMENT,
        self::OPERATOR_GREATER_THAN_OR_EQUAL => self::OPERATOR_GREATER_THAN_OR_EQUAL,
        self::OPERATOR_LESS_THAN_OR_EQUAL => self::OPERATOR_LESS_THAN_OR_EQUAL,
        self::OPERATOR_GREATER_THAN => self::OPERATOR_GREATER_THAN,
        self::OPERATOR_LESS_THAN => self::OPERATOR_LESS_THAN,
        self::OPERATOR_NOT_EQUAL => self::OPERATOR_NOT_EQUAL,
    ];

    protected $fillable = [
        'title',
        'key',
        'priority',
        'is_sequentially',
        'is_done_when_all_submitted',
        'config',
    ];

    protected $guarded = ['id'];

    public static function createApprovalIfMatch($key, $object)
    {
        $approvalConfigs = ApprovalConfigRepository::getByKey($key);

        foreach ($approvalConfigs as $approvalConfig) {
            if (self::isMatch(json_decode($approvalConfig->config, true), $object)) {
                $approval = ApprovalRepository::create([
                    'is_sequentially' => $approvalConfig->is_sequentially,
                    'is_done_when_all_submitted' => $approvalConfig->is_done_when_all_submitted,
                    'remarks_id' => $object->id,
                    'remarks_type' => get_class($object),
                ]);

                // Handle : Approval User
                foreach ($approvalConfig->approvalConfigUsers as $approvalConfigUser) {
                    $approvalUser = ApprovalUserRepository::create([
                        'approval_id' => $approval->id,
                        'user_id' => $approvalConfigUser->user_id,
                        'position' => $approvalConfigUser->position,
                    ]);

                    // Handle: Approval User Status Approval
                    foreach ($approvalConfigUser->approvalConfigUserStatusApprovals as $approvalConfigUserStatus) {
                        ApprovalUserStatusApprovalRepository::create([
                            'approval_user_id' => $approvalUser->id,
                            'status_approval_id' => $approvalConfigUserStatus->status_approval_id,
                        ]);
                    }
                }

                return $approval;
            }
        }

        return null;
    }

    public static function isMatch($config, $object)
    {
        $result = null;

        foreach ($config as $item) {
            $currentResult = false;

            if ($item['type'] === 'grouper') {
                if (isset($item['group']) && is_array($item['group'])) {
                    $currentResult = self::isMatch($item['group'], $object);
                }
            } else {
                $column = $item['column'];
                $operator = $item['operator'];
                $value = $item['value'];

                if (!data_get($object, $column)) {
                    $currentResult = false;
                } else {
                    $objectValue = data_get($object, $column);

                    switch ($operator) {
                        case self::OPERATOR_ASSIGNMENT:
                            $currentResult = ($objectValue == $value);
                            Log::info("The result for $column $objectValue $operator $value is " . ($currentResult ? "true" : "false"));
                            break;
                        case self::OPERATOR_NOT_EQUAL:
                            $currentResult = ($objectValue != $value);
                            Log::info("The result for $column $objectValue $operator $value is " . ($currentResult ? "true" : "false"));
                            break;
                        case self::OPERATOR_GREATER_THAN:
                            $currentResult = ($objectValue > $value);
                            Log::info("The result for $column $objectValue $operator $value is " . ($currentResult ? "true" : "false"));
                            break;
                        case self::OPERATOR_LESS_THAN:
                            $currentResult = ($objectValue < $value);
                            Log::info("The result for $column $objectValue $operator $value is " . ($currentResult ? "true" : "false"));
                            break;
                        case self::OPERATOR_GREATER_THAN_OR_EQUAL:
                            $currentResult = ($objectValue >= $value);
                            Log::info("The result for $column $objectValue $operator $value is " . ($currentResult ? "true" : "false"));
                            break;
                        case self::OPERATOR_LESS_THAN_OR_EQUAL:
                            $currentResult = ($objectValue <= $value);
                            Log::info("The result for $column $objectValue $operator $value is " . ($currentResult ? "true" : "false"));
                            break;
                        default:
                            throw new \InvalidArgumentException("Unsupported operator: $operator");
                    }
                }
            }

            $logic = $item['logic'];

            if (!$logic) {
                $result = $currentResult;
            } elseif ($logic === 'AND') {

                if ($result === null) {
                    $result = true;
                }
                $result = $result && $currentResult;
            } elseif ($logic === 'OR') {

                if ($result === null) {
                    $result = false;
                }
                $result = $result || $currentResult;
            }

            if ($logic === 'AND' && !$result) {
                break;
            }
        }

        return $result ?? true;
    }

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    /*
    | RELATIONSHIP
    */
    public function approvalConfigUsers()
    {
        return $this->hasMany(ApprovalConfigUser::class, 'approval_config_id', 'id');
    }
}
