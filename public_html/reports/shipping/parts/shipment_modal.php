<div class="modal" id="shipment_modal">
    <div class="modal-content">
        <h1>
            <span id="modal-title">Create/Edit Shipment</span>
            <span class="close shipment_modal_exit">×</span>
        </h1>
        <div id="shipment_form">
            <div class="input_group input_half">
                <label for="name">Name: </label>
                <input type="text" placeholder="Name" name="name" id="name" style="padding: 5px;background: none;border: none;border-bottom: 1px solid;"/>
            </div>
            <div class="input_group input_half">
                <label for="date_shipped">Date Shipped: </label>
                <input type="date" placeholder="2017-12-31" name="date_shipped" id="date_shipped"/>
            </div>
            <div class="input_group input_full">
                <label for="date_shipped">Description:</label>
                <input type="text" placeholder="Description" name="description" id="description"/>
            </div>
        </div>
        <div class="modal-footer">
            <a class="shipment_modal_save button">Save</a>
            <a class="shipment_modal_exit button">Close</a>
        </div>
    </div>
</div>
<script src="/reports/shipping/js/shipment_modal.js?v=3.0"></script>