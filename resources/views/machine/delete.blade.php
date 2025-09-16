<!-- Delete Machine Modal Partial -->
@foreach($machines as $machine)
<div class="modal fade" id="deleteMachineModal{{ $machine->id }}" tabindex="-1" aria-labelledby="deleteMachineModalLabel{{ $machine->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteMachineModalLabel{{ $machine->id }}">Delete Machine</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('machine.destroy', $machine->id) }}">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ $machine->machine_code }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
