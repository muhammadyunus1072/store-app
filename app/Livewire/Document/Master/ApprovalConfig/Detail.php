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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Document\Master\ApprovalConfig;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigRepository;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigUserRepository;

class Detail extends Component
{
    public $objId;
    public $object;

    #[Validate('required', message: 'Kunci Harus Diisi', onUpdate: false)]
    public $key;

    #[Validate('required', message: 'Priority Harus Diisi', onUpdate: false)]
    public $priority = 1;
    public $is_sequentially = false;

    public $config = [];

    public $config_logic_choice = [];
    public $config_default_logic_choice = [];
    public $config_type_choice = [];
    public $config_default_type_choice = [];
    public $config_operator_choice = [];
    public $config_default_operator_choice = [];

    public $approvalConfigUsers = [];
    public $approvalConfigUserRemoves = [];

    public function mount()
    {
        $this->config_logic_choice = ApprovalConfig::LOGIC_CHOICE;
        $this->config_default_logic_choice = ApprovalConfig::LOGIC_AND;
        $this->config_type_choice = ApprovalConfig::TYPE_CHOICE;
        $this->config_default_type_choice = ApprovalConfig::TYPE_COLUMN;
        $this->config_operator_choice = ApprovalConfig::OPERATOR_CHOICE;
        $this->config_default_operator_choice = ApprovalConfig::OPERATOR_ASSIGNMENT;

        if ($this->objId) {

            $id = Crypt::decrypt($this->objId);
            $approvalConfig = ApprovalConfigRepository::findWithDetails($id);

            $this->key = $approvalConfig->key;
            $this->priority = $approvalConfig->priority;
            $this->is_sequentially = $approvalConfig->is_sequentially;

            $this->config = json_decode($approvalConfig->config, true);

            foreach ($approvalConfig->approvalConfigUsers as $approvalConfigUser) {
                $this->approvalConfigUsers[] = [
                    'id' => Crypt::encrypt($approvalConfigUser->id),
                    'user_id' => Crypt::encrypt($approvalConfigUser->user_id),
                    'user_text' => $approvalConfigUser->user->name,
                    'key' => Str::random(30),
                    "position" => NumberFormatter::valueToImask($approvalConfigUser->position),
                ];
            }
        } else {
            $this->addConfig();
        }
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
                "logic" => $count ? $this->config_default_logic_choice : null,
                "type" => $this->config_default_type_choice,
                "operator" => $this->config_default_operator_choice,
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
                "logic" => $count ? $this->config_default_logic_choice : null,
                "type" => $this->config_default_type_choice,
                "operator" => $this->config_default_operator_choice,
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

    public function removeApprover($index)
    {
        if ($this->approvalConfigUsers[$index]['id']) {
            $this->approvalConfigUserRemoves[] = $this->approvalConfigUsers[$index]['id'];
        }

        unset($this->approvalConfigUsers[$index]);
    }

    public function updatedApprovalConfigUsers()
    {
        usort($this->approvalConfigUsers, function ($a, $b) {
            return $a['position'] > $b['position'];
        });
    }

    public function selectUser($data)
    {
        $data = $data['selectedOption'];
        $exists = collect($this->approvalConfigUsers)->contains('user_id', $data['id']);

        if (!$exists) {
            $this->approvalConfigUsers[] = [
                'id' => null,
                'user_id' => $data['id'],
                'user_text' => $data['text'],
                'key' => Str::random(30),
                "position" => count($this->approvalConfigUsers) + 1,
            ];
        }
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'key' => $this->key,
            'priority' => NumberFormatter::imaskToValue($this->priority),
            'is_sequentially' => $this->is_sequentially,
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

            foreach ($this->approvalConfigUsers as $index => $approvalConfigUser) {
                $validatedData = [
                    'approval_config_id' => $objId,
                    'user_id' => Crypt::decrypt($approvalConfigUser['user_id']),
                    'position' => NumberFormatter::imaskToValue($approvalConfigUser['position']),
                ];

                if (!$approvalConfigUser['id']) {
                    ApprovalConfigUserRepository::create($validatedData);
                } else {
                    ApprovalConfigUserRepository::update(Crypt::decrypt($approvalConfigUser['id']), $validatedData);
                }
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

    public function render()
    {
        return view('livewire.document.master.approval-config.detail');
    }
}
