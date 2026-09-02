<div id="expenseReport" class="expense-report">
    <div class="expense-report-hero">
        <div>
            <div class="expense-eyebrow"><span class="expense-live-dot"></span> Live petty-cash ledger</div>
            <h2>Expense report</h2>
            <p>One permission-scoped view of spend, ownership, accounting classification, workflow, and audit detail.</p>
        </div>
        <div class="expense-hero-actions">
            <button id="expenseExport" type="button" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export current view</button>
            <div id="expenseUpdatedAt" class="expense-updated">Loading current view…</div>
        </div>
    </div>

    <form id="expenseFilters" class="expense-filter-panel">
        <div class="expense-filter-toolbar">
            <div class="expense-filter-primary">
                <div class="expense-filter-field expense-date-field">
                    <label for="expenseStartDate">From</label>
                    <input id="expenseStartDate" name="startDate" class="form-control" type="date" value="<?php echo htmlspecialchars($yearStart, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="expense-filter-field expense-date-field">
                    <label for="expenseEndDate">To</label>
                    <input id="expenseEndDate" name="endDate" class="form-control" type="date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="expense-filter-field expense-search-field">
                    <label for="expenseSearch">Search all expense fields</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input id="expenseSearch" name="search" class="form-control" type="search" maxlength="100" placeholder="Notes, user, tab, code, GL or description">
                    </div>
                </div>
            </div>
            <div class="expense-filter-toolbar-actions">
                <span id="expenseFilterCount" class="expense-filter-count">All filters available</span>
                <button id="expenseAdvancedToggle" type="button" class="btn btn-default"><i class="fa fa-sliders"></i> All filters</button>
            </div>
        </div>

        <div id="expenseAdvancedFilters" class="expense-filter-advanced" aria-hidden="true">
            <div class="expense-filter-section-heading"><span>Scope and ownership</span><small>These filters apply to every chart, table, KPI, and export.</small></div>
            <div class="expense-filter-grid">
                <div class="expense-filter-field">
                    <label for="expenseUser">User / owner</label>
                    <select id="expenseUser" name="userCode" class="form-control"><option value="">All users</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseTabCode">Petty-cash tab</label>
                    <select id="expenseTabCode" name="tabCode" class="form-control"><option value="">All tabs</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseCostCenter">Cost centre</label>
                    <select id="expenseCostCenter" name="costCenter" class="form-control"><option value="">All cost centres</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseCurrency">Currency</label>
                    <select id="expenseCurrency" name="currency" class="form-control"><option value="">All currencies</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseIncludeLocalPurchases">Data scope</label>
                    <label class="expense-toggle-label"><input id="expenseIncludeLocalPurchases" name="includeLocalPurchases" type="checkbox" checked> Include local purchases</label>
                    <small>Exclude rows coded or described as Local Purchase when unchecked.</small>
                </div>
            </div>

            <div class="expense-filter-section-heading"><span>Accounting and workflow</span><small>Choose exact classification or control states.</small></div>
            <div class="expense-filter-grid">
                <div class="expense-filter-field">
                    <label for="expenseCategory">Executive category</label>
                    <select id="expenseCategory" name="category" class="form-control"><option value="">All categories</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseExpenseCode">Expense code</label>
                    <select id="expenseExpenseCode" name="expenseCode" class="form-control"><option value="">All expense codes</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseGlAccount">GL account</label>
                    <select id="expenseGlAccount" name="glAccount" class="form-control"><option value="">All GL accounts</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseAccountGroup">GL group</label>
                    <select id="expenseAccountGroup" name="accountGroup" class="form-control"><option value="">All GL groups</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseSection">Account section</label>
                    <select id="expenseSection" name="section" class="form-control"><option value="">All sections</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseSpendClass">Spend class</label>
                    <select id="expenseSpendClass" name="spendClass" class="form-control"><option value="">All spend classes</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseStatus">Workflow</label>
                    <select id="expenseStatus" name="status" class="form-control"><option value="">All statuses</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseReceipt">Evidence</label>
                    <select id="expenseReceipt" name="receipt" class="form-control"><option value="">All evidence states</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseEntryKind">Entry type</label>
                    <select id="expenseEntryKind" name="entryKind" class="form-control"><option value="">Expenses and credits</option></select>
                </div>
                <div class="expense-filter-field">
                    <label for="expenseMinAmount">Minimum amount</label>
                    <input id="expenseMinAmount" name="minAmount" class="form-control" type="number" min="0" step="0.01" placeholder="Any amount">
                </div>
                <div class="expense-filter-field">
                    <label for="expenseMaxAmount">Maximum amount</label>
                    <input id="expenseMaxAmount" name="maxAmount" class="form-control" type="number" min="0" step="0.01" placeholder="Any amount">
                </div>
            </div>
        </div>

        <div class="expense-filter-footer">
            <div class="expense-filter-state"><i class="fa fa-info-circle"></i> <span id="expenseFilterSummary">All expense data in the selected period</span></div>
            <div class="expense-filter-actions">
                <button id="expenseReset" type="button" class="btn btn-link">Reset</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Apply filters</button>
            </div>
        </div>
    </form>

    <div id="expenseAlert" class="alert expense-alert" role="alert" style="display:none;"></div>

    <div id="expenseLoading" class="expense-loading">
        <i class="fa fa-circle-o-notch fa-spin"></i>
        <strong>Building the expense report</strong>
        <span>Applying permissions, filters, currency conversion, and accounting classification…</span>
    </div>

    <div id="expenseReportContent" style="display:none;">
        <div class="expense-result-bar">
            <div>
                <strong id="expenseResultSummary">Current filtered view</strong>
                <div id="expenseActiveFilters" class="expense-active-filters"></div>
            </div>
            <div class="expense-result-meta">
                <span id="expenseValidationStatus" class="expense-validation-status">Validating totals…</span>
                <span id="expenseResultScope" class="expense-panel-meta"></span>
            </div>
        </div>

        <div class="expense-tabs" role="tablist" aria-label="Expense report sections">
            <button type="button" class="expense-tab is-active" role="tab" aria-selected="true" aria-controls="expenseTabSummary" data-expense-tab="summary"><i class="fa fa-dashboard"></i> Summary</button>
            <button type="button" class="expense-tab" role="tab" aria-selected="false" aria-controls="expenseTabUsers" data-expense-tab="users"><i class="fa fa-users"></i> User analysis</button>
            <button type="button" class="expense-tab" role="tab" aria-selected="false" aria-controls="expenseTabAccounting" data-expense-tab="accounting"><i class="fa fa-sitemap"></i> Accounting</button>
            <button type="button" class="expense-tab" role="tab" aria-selected="false" aria-controls="expenseTabTransactions" data-expense-tab="transactions"><i class="fa fa-list-alt"></i> Transactions <span id="expenseTransactionTabCount" class="expense-tab-count"></span></button>
        </div>

        <section id="expenseTabSummary" class="expense-tab-pane is-active" role="tabpanel">
            <div class="expense-kpi-grid">
                <article class="expense-kpi expense-kpi-primary"><span class="expense-kpi-label">Net spend</span><strong id="expenseNetSpend">—</strong><small id="expenseNetChange">—</small></article>
                <article class="expense-kpi"><span class="expense-kpi-label">P&amp;L spend</span><strong id="expensePnlSpend">—</strong><small id="expensePnlShare">Operating and direct costs</small></article>
                <article class="expense-kpi"><span class="expense-kpi-label">Capital &amp; advances</span><strong id="expenseBalanceSpend">—</strong><small>Separated from P&amp;L expense</small></article>
                <article class="expense-kpi expense-kpi-warning"><span class="expense-kpi-label">Needs action</span><strong id="expenseActionSpend">—</strong><small id="expenseActionDetail">Pending workflow</small></article>
                <article class="expense-kpi"><span class="expense-kpi-label">Transactions</span><strong id="expenseTransactions">—</strong><small id="expenseAverage">— average</small></article>
                <article class="expense-kpi"><span class="expense-kpi-label">Receipt coverage</span><strong id="expenseReceiptCoverage">—</strong><small id="expenseReceiptDetail">—</small></article>
            </div>
            <div id="expenseClassificationSummary" class="expense-classification-summary" aria-live="polite"></div>
            <div id="expenseInsights" class="expense-insight-grid"></div>

            <div class="expense-chart-grid expense-chart-grid-four">
                <article class="expense-panel expense-chart-panel expense-chart-panel-wide">
                    <header><div><span class="expense-panel-kicker">Trend</span><h3>Monthly spend</h3></div><span id="expenseTrendCaption" class="expense-panel-meta"></span></header>
                    <div class="expense-chart-wrap"><canvas id="expenseTrendChart"></canvas></div>
                    <div id="expenseTrendLegend" class="expense-chart-legend"></div>
                </article>
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Mix</span><h3>By category</h3></div><span class="expense-panel-meta">Net spend</span></header>
                    <div class="expense-chart-wrap expense-chart-wrap-donut"><canvas id="expenseCategoryChart"></canvas></div>
                    <div id="expenseCategoryLegend" class="expense-chart-legend"></div>
                </article>
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Controls</span><h3>Workflow status</h3></div><span class="expense-panel-meta">Amount and count</span></header>
                    <div class="expense-chart-wrap expense-chart-wrap-donut"><canvas id="expenseStatusChart"></canvas></div>
                    <div id="expenseStatusLegend" class="expense-chart-legend"></div>
                </article>
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Accountability</span><h3>By user</h3></div><span class="expense-panel-meta">All users</span></header>
                    <div class="expense-chart-wrap expense-chart-wrap-donut"><canvas id="expenseOwnerChart"></canvas></div>
                    <div id="expenseOwnerLegend" class="expense-chart-legend"></div>
                </article>
            </div>

            <article class="expense-panel expense-detail-panel">
                <header><div><span class="expense-panel-kicker">Complete breakdown</span><h3>Category totals</h3></div><span id="expenseCategoryCaption" class="expense-panel-meta"></span></header>
                <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>Category</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th><th class="text-right">Expense codes</th><th class="text-right">Change</th></tr></thead><tbody id="expenseCategoryTable"></tbody></table></div>
            </article>

            <div class="expense-two-column">
                <article class="expense-panel expense-detail-panel">
                    <header><div><span class="expense-panel-kicker">Complete control breakdown</span><h3>Workflow status</h3></div><span id="expenseStatusCaption" class="expense-panel-meta"></span></header>
                    <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>Status</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th></tr></thead><tbody id="expenseStatusTable"></tbody></table></div>
                </article>
                <article class="expense-panel expense-detail-panel">
                    <header><div><span class="expense-panel-kicker">Complete operating breakdown</span><h3>Cost centres</h3></div><span id="expenseCenterCaption" class="expense-panel-meta"></span></header>
                    <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>Cost centre</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th></tr></thead><tbody id="expenseCenterTable"></tbody></table></div>
                </article>
            </div>
        </section>

        <section id="expenseTabUsers" class="expense-tab-pane" role="tabpanel" hidden>
            <div class="expense-chart-grid expense-chart-grid-two">
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Consolidated ownership</span><h3>Spend by user</h3></div><span class="expense-panel-meta">All users</span></header>
                    <div class="expense-chart-wrap"><canvas id="expenseUserChart"></canvas></div>
                    <div id="expenseUserLegend" class="expense-chart-legend"></div>
                </article>
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Granular ownership</span><h3>User and expense code</h3></div><span id="expenseUserExpenseChartCaption" class="expense-panel-meta"></span></header>
                    <div class="expense-chart-wrap"><canvas id="expenseUserExpenseChart"></canvas></div>
                    <div id="expenseUserExpenseLegend" class="expense-chart-legend"></div>
                </article>
            </div>
            <article class="expense-panel expense-detail-panel">
                <header><div><span class="expense-panel-kicker">Complete user-wise consolidated view</span><h3>Spend by user</h3></div><span id="expenseUserCaption" class="expense-panel-meta"></span></header>
                <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>User</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th><th class="text-right">Tabs</th><th class="text-right">Codes</th><th class="text-right">P&amp;L spend</th><th class="text-right">Capital / advances</th><th class="text-right">Unclassified</th><th class="text-right">Receipts</th><th class="text-right">Change</th></tr></thead><tbody id="expenseUserTable"></tbody></table></div>
            </article>
            <article class="expense-panel expense-detail-panel">
                <header><div><span class="expense-panel-kicker">Complete user-wise granular view</span><h3>User, expense code, and GL detail</h3></div><span id="expenseUserExpenseCaption" class="expense-panel-meta"></span></header>
                <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>User</th><th>Category</th><th>Expense code</th><th>Description</th><th>GL account / group</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th><th class="text-right">Tabs</th><th class="text-right">Change</th></tr></thead><tbody id="expenseUserExpenseTable"></tbody></table></div>
            </article>
        </section>

        <section id="expenseTabAccounting" class="expense-tab-pane" role="tabpanel" hidden>
            <div class="expense-chart-grid expense-chart-grid-three">
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Classification</span><h3>Expense codes</h3></div><span class="expense-panel-meta">Net spend</span></header>
                    <div class="expense-chart-wrap"><canvas id="expenseCodeChart"></canvas></div>
                    <div id="expenseCodeLegend" class="expense-chart-legend"></div>
                </article>
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Ledger structure</span><h3>GL groups</h3></div><span class="expense-panel-meta">Net spend</span></header>
                    <div class="expense-chart-wrap"><canvas id="expenseGlChart"></canvas></div>
                    <div id="expenseGlLegend" class="expense-chart-legend"></div>
                </article>
                <article class="expense-panel expense-chart-panel">
                    <header><div><span class="expense-panel-kicker">Operating structure</span><h3>Cost centres</h3></div><span class="expense-panel-meta">Net spend</span></header>
                    <div class="expense-chart-wrap"><canvas id="expenseAccountingCenterChart"></canvas></div>
                    <div id="expenseAccountingCenterLegend" class="expense-chart-legend"></div>
                </article>
            </div>
            <article class="expense-panel expense-detail-panel">
                <header><div><span class="expense-panel-kicker">Complete accounting view</span><h3>Expense codes and GL classification</h3></div><span id="expenseCodeCaption" class="expense-panel-meta"></span></header>
                <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>Executive category</th><th>Expense code</th><th>Description</th><th>GL account</th><th>GL group</th><th>Account section</th><th>Spend class</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th><th class="text-right">Change</th></tr></thead><tbody id="expenseCodeTable"></tbody></table></div>
            </article>
            <article class="expense-panel expense-detail-panel">
                <header><div><span class="expense-panel-kicker">Complete ledger grouping</span><h3>GL group totals</h3></div><span id="expenseGlCaption" class="expense-panel-meta"></span></header>
                <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>GL group</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th><th class="text-right">Expense codes</th></tr></thead><tbody id="expenseGlTable"></tbody></table></div>
            </article>
            <article class="expense-panel expense-detail-panel">
                <header><div><span class="expense-panel-kicker">Currency reconciliation</span><h3>Original and functional currency</h3></div><span id="expenseCurrencyCaption" class="expense-panel-meta"></span></header>
                <div class="table-responsive expense-table-wrap"><table class="table expense-table"><thead><tr><th>Currency</th><th class="text-right">Current rate</th><th class="text-right">Original amount</th><th class="text-right">Functional net spend</th><th class="text-right">Transactions</th></tr></thead><tbody id="expenseCurrencyTable"></tbody></table></div>
            </article>
        </section>

        <section id="expenseTabTransactions" class="expense-tab-pane" role="tabpanel" hidden>
            <article class="expense-panel expense-detail-panel">
                <header>
                    <div><span class="expense-panel-kicker">Audit-ready detail</span><h3>All matching transactions</h3></div>
                    <div class="expense-table-tools">
                        <label>Sort <select id="expenseSort" class="form-control input-sm"><option value="date">Date</option><option value="amount">Amount</option><option value="category">Category</option><option value="description">Description</option><option value="owner">User</option><option value="status">Workflow</option></select></label>
                        <button id="expenseSortDirection" type="button" class="btn btn-default btn-sm" data-direction="desc" title="Sort descending"><i class="fa fa-sort-amount-desc"></i></button>
                        <label>Rows <select id="expensePageSize" class="form-control input-sm"><option value="25">25</option><option value="50" selected>50</option><option value="100">100</option><option value="200">200</option></select></label>
                    </div>
                </header>
                <div class="expense-table-caption"><span id="expenseTransactionCaption"></span><span>Use the filters above to narrow every row and aggregate.</span></div>
                <div class="table-responsive expense-table-wrap"><table class="table expense-table expense-transaction-table"><thead><tr><th>Date</th><th>Category &amp; code</th><th>User / tab</th><th>GL classification</th><th>Workflow</th><th>Notes</th><th>Evidence</th><th class="text-right">Amount</th></tr></thead><tbody id="expenseTransactionTable"></tbody></table></div>
                <footer class="expense-pagination"><button id="expensePreviousPage" type="button" class="btn btn-default btn-sm"><i class="fa fa-angle-left"></i> Previous</button><span id="expensePageLabel">Page 1</span><button id="expenseNextPage" type="button" class="btn btn-default btn-sm">Next <i class="fa fa-angle-right"></i></button></footer>
            </article>
        </section>

    </div>
</div>
