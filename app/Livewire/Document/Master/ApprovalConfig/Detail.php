<?php

namespace App\Livewire\Document\Master\ApprovalConfig;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Document\Master\ApprovalConfig;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigRepository;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigUserRepository;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigUserStatusApprovalRepository;
use App\Repositories\Document\Master\StatusApproval\StatusApprovalRepository;

class Detail extends Component
{
    public $objId;
    public $object;

    #[Validate('required', message: 'Kunci Harus Diisi', onUpdate: false)]
    public $key;
    #[Validate('required', message: 'Priority Harus Diisi', onUpdate: false)]
    public $priority = 1;
    public $isSequentially = false;
    public $config = [];

    public $approvalConfigUsers = [];
    public $approvalConfigUserRemoves = [];

    // Helpers
    public $configLogicChoice = [];
    public $configDefaultLogicChoice = [];
    public $configTypeChoice = [];
    public $configDefaultTypeChoice = [];
    public $configOperatorChoice = [];
    public $configDefaultOperatorChoice = [];

    public $statusApprovalChoices = [];

    public function render()
    {
        return view('livewire.document.master.approval-config.detail');
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('approval_config.edit', $this->objId);
        } else {
            $this->redirectRoute('approval_config.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('approval_config.index');
    }

    public function mount()
    {
        $this->configLogicChoice = ApprovalConfig::LOGIC_CHOICE;
        $this->configDefaultLogicChoice = ApprovalConfig::LOGIC_AND;
        $this->configTypeChoice = ApprovalConfig::TYPE_CHOICE;
        $this->configDefaultTypeChoice = ApprovalConfig::TYPE_COLUMN;
        $this->configOperatorChoice = ApprovalConfig::OPERATOR_CHOICE;
        $this->configDefaultOperatorChoice = ApprovalConfig::OPERATOR_ASSIGNMENT;

        $statusApprovals = StatusApprovalRepository::all();
        foreach ($statusApprovals as $item) {
            $this->statusApprovalChoices[] = [
                'id' => $item->id,
                'text' => $item->name
            ];
        }

        if ($this->objId) {
            $approvalConfig = ApprovalConfigRepository::findWithDetails(Crypt::decrypt($this->objId));

            $this->key = $approvalConfig->key;
            $this->priority = $approvalConfig->priority;
            $this->isSequentially = $approvalConfig->is_sequentially;
            $this->config = json_decode($approvalConfig->config, true);

            foreach ($approvalConfig->approvalConfigUsers as $index => $approvalConfigUser) {
                $this->approvalConfigUsers[$index] = [
                    'id' => Crypt::encrypt($approvalConfigUser->id),
                    'key' => Str::random(30),
                    'user_id' => Crypt::encrypt($approvalConfigUser->user_id),
                    'user_text' => $approvalConfigUser->user->name,
                    "position" => NumberFormatter::valueToImask($approvalConfigUser->position),
                    "status_approvals" => $approvalConfigUser->approvalConfigUserStatusApprovals()->pluck('status_approval_id')->toArray(),
                ];
            }
        } else {
            $this->addConfig();
        }
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'key' => $this->key,
            'priority' => NumberFormatter::imaskToValue($this->priority),
            'is_sequentially' => $this->isSequentially,
            'config' => json_encode($this->config),
        ];

        try {
            DB::beginTransaction();

            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                ApprovalConfigRepository::update($objId, $validatedData);
            } else {
                $obj = ApprovalConfigRepository::create($validatedData);
                $objId = $obj->id;
            }

            // Handle Approval Config User
            foreach ($this->approvalConfigUsers as $approvalConfigUser) {
                $validatedData = [
                    'approval_config_id' => $objId,
                    'user_id' => Crypt::decrypt($approvalConfigUser['user_id']),
                    'position' => NumberFormatter::imaskToValue($approvalConfigUser['position']),
                ];

                if (!$approvalConfigUser['id']) {
                    $approvalUser = ApprovalConfigUserRepository::create($validatedData);
                    $approvalConfigUserId = $approvalUser->id;
                } else {
                    ApprovalConfigUserRepository::update(Crypt::decrypt($approvalConfigUser['id']), $validatedData);
                    $approvalConfigUserId = Crypt::decrypt($approvalConfigUser['id']);
                }

                // Handle Approval Config User Status Approval
                foreach ($approvalConfigUser['status_approvals'] as $id) {
                    ApprovalConfigUserStatusApprovalRepository::createIfNotExist([
                        'approval_config_user_id' => $approvalConfigUserId,
                        'status_approval_id' => $id,
                    ]);
                }
                ApprovalConfigUserStatusApprovalRepository::deleteExcept($approvalConfigUserId, $approvalConfigUser['status_approvals']);
            }

            foreach ($this->approvalConfigUserRemoves as $item) {
                ApprovalConfigUserRepository::delete(Crypt::decrypt($item));
            }

            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Akses Berhasil Diperbarui",
                "on-dialog-confirm",
                "on-dialog-cancel",
                "Oke",
                "Tutup",
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    // HANDLE CONFIG
    public function updated($property, $value)
    {
        if (str_contains($property, 'config')) {
            if (str_contains($property, 'type')) {
                if ($value == 'column') {
                    $index = str_replace("config.", "", $property);
                    $index = str_replace(".type", "", $index);

                    $indexes = explode('.group.', $index);
                    $configPointer = &$this->config;

                    foreach ($indexes as $in => $i) {
                        $i = (int)$i;

                        if ($in == count($indexes) - 1) {
                            if (isset($configPointer[$i]['group'])) {
                                $configPointer = &$configPointer[$i]['group'];
                                $this->dispatch('consoleLog', $configPointer);
                                foreach ($configPointer as $index => $toRemove) {
                                    $index = (int)$index;
                                    $this->dispatch('consoleLog', $index);
                                    unset($configPointer[$index]);
                                }
                            }
                        } elseif (isset($configPointer[$i]['group'])) {
                            $configPointer = &$configPointer[$i]['group'];
                        }
                    }
                    $this->config = array_values($this->config);
                } else if ($value = 'grouper') {
                    $index = str_replace("config.", "", $property);
                    $index = str_replace(".type", "", $index);

                    $this->addConfig($index);
                }
            }
        }
    }

    public function addConfig($index = null)
    {
        if ($index === null) {

            $count = count($this->config);
            $this->config[] = [
                "key" => Str::random(30),
                "logic" => $count ? $this->configDefaultLogicChoice : null,
                "type" => $this->configDefaultTypeChoice,
                "operator" => $this->configDefaultOperatorChoice,
                "column" => null,
                "value" => null,
                "group" => [],
            ];
        } else {

            $indexes = explode(".group.", $index);
            $nested_count = count($indexes);
            $configPointer = &$this->config;

            foreach ($indexes as $i) {
                $i = (int)$i;
                if (!isset($configPointer[$i]['group'])) {
                    $configPointer[$i]['group'] = [];
                }
                $configPointer = &$configPointer[$i]['group'];
            }

            $count = count($configPointer);
            $this->dispatch('consoleLog', $count);
            $configPointer[] = [
                "key" => Str::random(30),
                "logic" => $count ? $this->configDefaultLogicChoice : null,
                "type" => $this->configDefaultTypeChoice,
                "operator" => $this->configDefaultOperatorChoice,
                "column" => null,
                "value" => null,
            ];
        }
    }

    public function removeConfig($index)
    {
        $indexes = explode('.group.', $index);
        $configPointer = &$this->config;

        foreach ($indexes as $in => $i) {
            $i = (int)$i;

            if ($in == count($indexes) - 1) {
                unset($configPointer[$i]);
            } elseif (isset($configPointer[$i]['group'])) {
                $configPointer = &$configPointer[$i]['group'];
            }
        }
        $this->config = array_values($this->config);
    }

    // HANDLE APPROVER
    public function addApprover($data)
    {
        $exists = collect($this->approvalConfigUsers)->contains('user_id', $data['id']);

        if (!$exists) {
            $this->approvalConfigUsers[] = [
                'id' => null,
                'user_id' => $data['id'],
                'user_text' => $data['text'],
                'key' => Str::random(30),
                "position" => count($this->approvalConfigUsers) + 1,
                "status_approvals" => [],
            ];
        }

        $this->dispatch("init-select2-status-approval");
    }

    public function removeApprover($index)
    {
        if ($this->approvalConfigUsers[$index]['id']) {
            $this->approvalConfigUserRemoves[] = $this->approvalConfigUsers[$index]['id'];
        }

        unset($this->approvalConfigUsers[$index]);
        
        $this->dispatch("init-select2-status-approval");
    }

    public function updatedApprovalConfigUsers()
    {
        usort($this->approvalConfigUsers, function ($a, $b) {
            return $a['position'] > $b['position'];
        });
    }

    // HANDLE APPROVER STATUS APPROVAL
    public function addApproverStatusApproval($indexUser, $statusApprovalId)
    {
        $this->approvalConfigUsers[$indexUser]['status_approvals'][] = $statusApprovalId;
    }

    public function removeApproverStatusApproval($indexUser, $statusApprovalId)
    {
        $index = array_search($statusApprovalId, $this->approvalConfigUsers[$indexUser]['status_approvals']);

        if ($index !== false) {
            unset($this->approvalConfigUsers[$indexUser]['status_approvals'][$index]);
        }
    }
}
