<?php
/*
 * This endpoint used to rewrite stockmoves.newqoh by mixing location,
 * substore, salesperson, and pre-market-slip balances. It is intentionally
 * disabled; use the scope-aware inventory audit instead.
 */
http_response_code(410);
exit('Legacy stock movement rectification is disabled.');
