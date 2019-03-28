<div class="modal" id="tracking_number_modal">
    <div class="modal-content">
        <h1>
            <span id="modal-title">Create/Edit Tracking Number</span>
            <span class="close tracking_number_modal_exit">×</span>
        </h1>
        <div id="shipment_form">
            <div class="input_group input_half">
                <label for="tracking_number">Tracking Number: </label>
                <input type="text" placeholder="Tracking Number" name="tracking_number" id="tracking_number" style="padding:5px;background:none;border:none;border-bottom: 1px solid;"/>
            </div>
            <div class="input_group input_half">
                <label for="tracking_number">Provider: </label>
                <select id="shipping_provider">
                    <option value="UPS">UPS</option>
                    <option value="USPS">USPS</option>
                    <option value="Amazon">Amazon</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <a class="tracking_number_modal_save button">Save</a>
            <a class="tracking_number_modal_exit button">Close</a>
        </div>
    </div>
</div>
<script src="/reports/shipping/js/tracking_number_modal.js?v=1.2"></script>