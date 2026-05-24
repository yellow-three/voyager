<?php
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public bool $isImpersonating = false;

    public function mount(): void
    {
        $this->isImpersonating = session()->has('impersonator_id');
    }

    public function stopImpersonating(): void
    {
        if (session()->has('impersonator_id')) {
            $originalId = session()->pull('impersonator_id');
            Auth::loginUsingId($originalId);
            
            $this->redirect(route('voyager.users.index'));
        }
    }
};
?>

@if($isImpersonating)
    <div class="bg-amber-500 text-white px-4 py-2 flex items-center justify-between text-xs font-bold shadow-md animate-pulse">
        <div class="flex items-center gap-2">
            <i class="voyager-warning text-sm"></i>
            <span>You are currently impersonating <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }}).</span>
        </div>
        <button type="button" wire:click="stopImpersonating" class="bg-white text-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50 transition-all flex items-center gap-1">
            <i class="voyager-angle-right"></i>
            Exit Session
        </button>
    </div>
@endif
