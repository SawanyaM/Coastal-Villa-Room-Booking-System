
<section class="availability-section">
    <div class="availability-heading">
        <p class="section-label">PLAN YOUR STAY</p>
        <h2>Find Your Perfect Stay</h2>
        <p>Choose your dates and number of guests.</p>
    </div>

    <form action="availability.php" method="GET"
          class="availability-form">

        <div class="form-group">
            <label for="check_in">Check-in</label>
            <input type="date" id="check_in" name="check_in"
                   min="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label for="check_out">Check-out</label>
            <input type="date" id="check_out" name="check_out"
                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
        </div>

        <div class="form-group">
            <label for="guests">Guests</label>
            <select id="guests" name="guests" required>
                <option value="1">1 Guest</option>
                <option value="2" selected>2 Guests</option>
                <option value="3">3 Guests</option>
                <option value="4">4 Guests</option>
                <option value="5">5 Guests</option>
                <option value="6">6 Guests</option>
            </select>
        </div>

        <button type="submit" class="primary-btn">
            Search Available Rooms
        </button>

    </form>
</section>
