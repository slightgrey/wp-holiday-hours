/**
 * Holiday Hours Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        let holidays = window.holidayData || [];
        let editingId = null;

        /**
         * Add next year
         */
        $('#add-next-year-btn').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);

            // Find the maximum year from the dropdown
            let maxYear = new Date().getFullYear();
            $('#year-select option').each(function() {
                const year = parseInt($(this).val());
                if (year > maxYear) {
                    maxYear = year;
                }
            });

            const nextYear = maxYear + 1;

            $btn.prop('disabled', true).text('Adding...');

            $.ajax({
                url: holidayHoursAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'holiday_hours_add_year',
                    nonce: holidayHoursAjax.nonce,
                    year: nextYear
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert(response.data.message || 'Failed to add year.');
                        $btn.prop('disabled', false).text('+ Add Next Year');
                    }
                },
                error: function() {
                    alert('An error occurred while adding year.');
                    $btn.prop('disabled', false).text('+ Add Next Year');
                }
            });
        });

        /**
         * Open modal for adding new holiday
         */
        $('#add-holiday-btn').on('click', function(e) {
            e.preventDefault();
            openModal();
        });

        /**
         * Open modal for editing holiday
         */
        $(document).on('click', '.edit-holiday-btn', function(e) {
            e.preventDefault();
            const holidayId = $(this).data('holiday-id');
            const holiday = holidays.find(h => h.id == holidayId);
            if (holiday) {
                openModal(holiday);
            }
        });

        /**
         * Delete holiday
         */
        $(document).on('click', '.delete-holiday-btn', function(e) {
            e.preventDefault();
            const holidayId = $(this).data('holiday-id');

            if (confirm('Are you sure you want to delete this holiday schedule?')) {
                const $btn = $(this);
                $btn.prop('disabled', true).text('Deleting...');

                // AJAX delete from database
                $.ajax({
                    url: holidayHoursAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'holiday_hours_delete',
                        nonce: holidayHoursAjax.nonce,
                        id: holidayId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Reload page to show updated data
                            window.location.reload();
                        } else {
                            alert(response.data.message || 'Failed to delete holiday schedule.');
                            $btn.prop('disabled', false).text('Delete');
                        }
                    },
                    error: function() {
                        alert('An error occurred while deleting.');
                        $btn.prop('disabled', false).text('Delete');
                    }
                });
            }
        });

        /**
         * Close modal
         */
        $('.holiday-modal-close, #modal-cancel-btn').on('click', function() {
            closeModal();
        });

        /**
         * Close modal when clicking outside
         */
        $(window).on('click', function(e) {
            if (e.target.id === 'holiday-modal') {
                closeModal();
            }
        });

        /**
         * Toggle hours/custom text fields in modal
         */
        $('.modal-status-radio').on('change', function() {
            const status = $(this).val();
            if (status === 'open') {
                $('.modal-hours-fields').show();
                $('.modal-custom-text-field').hide();
            } else {
                $('.modal-hours-fields').hide();
                $('.modal-custom-text-field').show();
            }
        });

        /**
         * Save holiday from modal
         */
        $('#modal-save-btn').on('click', function() {
            const dateFrom = $('#modal-date-from').val();
            const dateTo = $('#modal-date-to').val();
            const status = $('.modal-status-radio:checked').val();
            const openTime = $('#modal-open-time').val();
            const closeTime = $('#modal-close-time').val();
            const customText = $('#modal-custom-text').val();

            // Validation
            if (!dateFrom) {
                alert('Please select a "From" date.');
                return;
            }

            // Validate date range
            if (dateTo && dateFrom && dateTo < dateFrom) {
                alert('The "To" date cannot be earlier than the "From" date.');
                return;
            }

            if (status === 'open' && (!openTime || !closeTime)) {
                alert('Please fill in both open and close times.');
                return;
            }

            if (status === 'closed' && !customText) {
                alert('Please enter a custom message.');
                return;
            }

            // Disable button and show loading
            const $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            // AJAX save to database
            $.ajax({
                url: holidayHoursAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'holiday_hours_save',
                    nonce: holidayHoursAjax.nonce,
                    id: editingId || 0,
                    date_from: dateFrom,
                    date_to: dateTo || '',
                    status: status,
                    open_time: openTime || '',
                    close_time: closeTime || '',
                    custom_text: customText || ''
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show updated data
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'Failed to save holiday schedule.');
                        $btn.prop('disabled', false).text('Save Holiday');
                    }
                },
                error: function() {
                    alert('An error occurred while saving.');
                    $btn.prop('disabled', false).text('Save Holiday');
                }
            });
        });

        /**
         * Open modal
         */
        function openModal(holiday = null) {
            if (holiday) {
                // Edit mode
                editingId = holiday.id;
                $('#modal-title').text('Edit Holiday Schedule');
                $('#modal-date-from').val(holiday.date_from);
                $('#modal-date-to').val(holiday.date_to || '');
                $('input[name="modal-status"][value="' + holiday.status + '"]').prop('checked', true).trigger('change');
                $('#modal-open-time').val(holiday.open_time || '');
                $('#modal-close-time').val(holiday.close_time || '');
                $('#modal-custom-text').val(holiday.custom_text || '');
            } else {
                // Add mode
                editingId = null;
                $('#modal-title').text('Add Holiday Schedule');
                $('#modal-date-from').val('');
                $('#modal-date-to').val('');
                $('input[name="modal-status"][value="open"]').prop('checked', true).trigger('change');
                $('#modal-open-time').val('');
                $('#modal-close-time').val('');
                $('#modal-custom-text').val('');
            }

            $('#holiday-modal').fadeIn(200);
        }

        /**
         * Close modal
         */
        function closeModal() {
            $('#holiday-modal').fadeOut(200);
            editingId = null;
        }

    });

})(jQuery);
