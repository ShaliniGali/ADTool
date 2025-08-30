<div id="to_cut-container"class="d-flex flex-row">
    <label for="to_cut" class="bx--label mr-1">To Cut<br />
        <select id="to_cut">
          <option></option>
            <?php for($i = 100; $i >= 0; $i--): ?>
                <option value="<?= $i ?>"><?=$i?>%</option>
            <?php endfor; ?>
        </select>
    </label>
</div>

<script>
    $('#to_cut').select2({
        placeholder: "Select an Percentage",
        allowClear: true,
        width: '12vw',
        matcher: function(params, data) {
          // Do not display the item if there is no 'text' property
          if (typeof data.text === 'undefined') {
            return null;
          }

          // return exact matches only
          if (data.text === `${params.term}%`) {
            var modifiedData = $.extend({}, data, true);

            return modifiedData;
          }

          // Return `null` if the term should not be displayed
          return null;
        },
        language: {
            noResults: function() {
              return "";
            }
        }
  });
</script>