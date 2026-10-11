<div class="crud-actions">
<a href="{{ $editUrl }}" class="crud-edit" title="Edit" aria-label="Edit record"><i class="bx bx-edit" aria-hidden="true"></i></a>
@if($deleteUrl ?? null)
<form action="{{ $deleteUrl }}" method="POST" class="d-inline" onsubmit="return confirm({{ json_encode($confirmation ?? 'Delete this record?') }})">
    @csrf @method('DELETE')
    <button class="btn btn-link text-danger p-0" type="submit" title="{{ $deleteLabel ?? 'Delete' }}" aria-label="{{ $deleteLabel ?? 'Delete record' }}"><i class="bx bx-trash" aria-hidden="true"></i></button>
</form>
@endif
</div>
