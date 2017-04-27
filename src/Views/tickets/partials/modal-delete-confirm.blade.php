<!-- Modal Dialog -->
<div class="modal fade" id="confirmDelete" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">{{ trans('brazidesk::lang.flash-x') }}</button>
        <h4 class="modal-title">{{ trans('brazidesk::lang.show-ticket-modal-delete-title') }}</h4>
      </div>
      <div class="modal-body">
        <p>{{ trans('brazidesk::lang.show-ticket-modal-delete-message') }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('brazidesk::lang.btn-cancel') }}</button>
        <button type="button" class="btn btn-danger" id="confirm">{{ trans('brazidesk::lang.btn-delete') }}</button>
      </div>
    </div>
  </div>
</div>
