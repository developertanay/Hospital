<div class="col-md-6"><label class="form-label">Workspace group</label><input class="form-control" name="care_group" maxlength="50" value="{{ old('care_group', optional($data ?? null)->care_group) }}" placeholder="e.g. Hospital management"></div>
<div class="col-md-6"><p class="mt-4">Use Role Assignments to choose which roles can see this module.</p></div>
