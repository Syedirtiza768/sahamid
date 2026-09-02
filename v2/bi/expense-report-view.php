<div id="expenseReport" class="expense-report">
    <div class="expense-report-hero">
        <div>
            <div class="expense-eyebrow"><span class="expense-live-dot"></span> Live petty-cash ledger</div>
            <h2>Executive expense intelligence</h2>
            <p>See where money went, who owns it, what needs action, and the accounting classification—without building a report first.</p>
        </div>
        <div class="expense-hero-actions">
            <button id="expenseExport" type="button" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export full report</button>
            <div id="expenseUpdatedAt" class="expense-updated">Loading current view…</div>
        </div>
    </div>

    <form id="expenseFilters" class="expense-filter-panel">
        <div class="expense-filter-field expense-date-field">
            <label for="expenseStartDate">From</label>
            <input id="expenseStartDate" name="startDate" class="form-control" type="date" value="<?php echo htmlspecialchars($yearStart, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="expense-filter-field expense-date-field">
            <label for="expenseEndDate">To</label>
            <input id="expenseEndDate" name="endDate" class="form-control" type="date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="expense-filter-field">
            <label for="expenseCategory">Category</label>
            <select id="expenseCategory" name="category" class="form-control"><option value="">All categories</option></select>
        </div>
        <div class="expense-filter-field">
            <label for="expenseCostCenter">Cost centre</label>
            <select id="expenseCostCenter" name="costCenter" class="form-control"><option value="">All cost centres</option></select>
        </div>
        <div class="expense-filter-field">
            <label for="expenseStatus">Workflow</label>
            <select id="expenseStatus" name="status" class="form-control"><option value="">All statuses</option></select>
        </div>
        <div class="expense-filter-field">
            <label for="expenseCurrency">Currency</label>
            <select id="expenseCurrency" name="currency" class="form-control"><option value="">All currencies</option></select>
        </div>
        <div class="expense-filter-field expense-search-field">
            <label for="expenseSearch">Find</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input id="expenseSearch" name="search" class="form-control" type="search" maxlength="100" placeholder="Code, notes, owner or tab">
            </div>
        </div>
        <div class="expense-filter-actions">
            <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Update view</button>
            <button id="expenseReset" type="button" class="btn btn-link">Reset</button>
        </div>
    </form>

    <div id="expenseAlert" class="alert expense-alert" role="alert" style="display:none;"></div>

    <div id="expenseLoading" class="expense-loading">
        <i class="fa fa-circle-o-notch fa-spin"></i>
        <strong>Building the executive view</strong>
        <span>Classifying spend, workflow, ownership, and accounting detail…</span>
    </div>

    <div id="expenseReportContent" style="display:none;">
        <div class="expense-kpi-grid">
            <article class="expense-kpi expense-kpi-primary">
                <span class="expense-kpi-label">Net spend</span>
                <strong id="expenseNetSpend">—</strong>
                <small id="expenseNetChange">—</small>
            </article>
            <article class="expense-kpi">
                <span class="expense-kpi-label">P&amp;L spend</span>
                <strong id="expensePnlSpend">—</strong>
                <small id="expensePnlShare">Operating and direct costs</small>
            </article>
            <article class="expense-kpi">
                <span class="expense-kpi-label">Capital &amp; advances</span>
                <strong id="expenseBalanceSpend">—</strong>
                <small>Separated from P&amp;L expense</small>
            </article>
            <article class="expense-kpi expense-kpi-warning">
                <span class="expense-kpi-label">Needs action</span>
                <strong id="expenseActionSpend">—</strong>
                <small id="expenseActionDetail">Pending workflow</small>
            </article>
            <article class="expense-kpi">
                <span class="expense-kpi-label">Transactions</span>
                <strong id="expenseTransactions">—</strong>
                <small id="expenseAverage">— average</small>
            </article>
            <article class="expense-kpi">
                <span class="expense-kpi-label">Receipt coverage</span>
                <strong id="expenseReceiptCoverage">—</strong>
                <small id="expenseReceiptDetail">—</small>
            </article>
        </div>

        <div id="expenseInsights" class="expense-insight-grid"></div>

        <div class="row expense-visual-row">
            <div class="col-lg-7">
                <article class="expense-panel expense-chart-panel">
                    <header>
                        <div><span class="expense-panel-kicker">Momentum</span><h3>Monthly spend trend</h3></div>
                        <span id="expenseTrendCaption" class="expense-panel-meta"></span>
                    </header>
                    <div class="expense-chart-wrap"><canvas id="expenseTrendChart" height="120"></canvas></div>
                </article>
            </div>
            <div class="col-lg-5">
                <article class="expense-panel">
                    <header>
                        <div><span class="expense-panel-kicker">Mix</span><h3>Where the money went</h3></div>
                        <span class="expense-panel-meta">Share of net spend</span>
                    </header>
                    <div id="expenseCategoryBars" class="expense-bars"></div>
                </article>
            </div>
        </div>

        <div class="row expense-visual-row">
            <div class="col-lg-7">
                <article class="expense-panel">
                    <header>
                        <div><span class="expense-panel-kicker">Category performance</span><h3>Consolidated view</h3></div>
                        <span class="expense-panel-meta">Current vs preceding equal period</span>
                    </header>
                    <div class="table-responsive expense-table-wrap">
                        <table class="table expense-table">
                            <thead><tr><th>Category</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th><th class="text-right">Change</th></tr></thead>
                            <tbody id="expenseCategoryTable"></tbody>
                        </table>
                    </div>
                </article>
            </div>
            <div class="col-lg-5">
                <article class="expense-panel">
                    <header>
                        <div><span class="expense-panel-kicker">Controls</span><h3>Workflow &amp; evidence</h3></div>
                        <span class="expense-panel-meta">What needs attention</span>
                    </header>
                    <div id="expenseStatusList" class="expense-status-list"></div>
                    <div id="expenseQuality" class="expense-quality-grid"></div>
                </article>
            </div>
        </div>

        <div class="row expense-visual-row">
            <div class="col-lg-6">
                <article class="expense-panel">
                    <header>
                        <div><span class="expense-panel-kicker">Accountability</span><h3>Top cost owners</h3></div>
                        <span class="expense-panel-meta">Expense tab ownership</span>
                    </header>
                    <div id="expenseOwnerBars" class="expense-bars expense-owner-bars"></div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="expense-panel">
                    <header>
                        <div><span class="expense-panel-kicker">Operating structure</span><h3>Cost centres</h3></div>
                        <span class="expense-panel-meta">Tab type / project grouping</span>
                    </header>
                    <div id="expenseCenterBars" class="expense-bars expense-owner-bars"></div>
                </article>
            </div>
        </div>

        <article class="expense-panel expense-detail-panel">
            <header>
                <div><span class="expense-panel-kicker">Granular accounting view</span><h3>Expense codes &amp; GL classification</h3></div>
                <span id="expenseCodeCaption" class="expense-panel-meta"></span>
            </header>
            <div class="table-responsive expense-table-wrap expense-code-table-wrap">
                <table class="table expense-table">
                    <thead><tr><th>Executive category</th><th>Expense code</th><th>Description</th><th>GL account</th><th>GL group</th><th>Spend class</th><th class="text-right">Net spend</th><th class="text-right">Share</th><th class="text-right">Transactions</th></tr></thead>
                    <tbody id="expenseCodeTable"></tbody>
                </table>
            </div>
        </article>

        <article class="expense-panel expense-detail-panel">
            <header>
                <div><span class="expense-panel-kicker">Audit-ready detail</span><h3>Transactions</h3></div>
                <span id="expenseTransactionCaption" class="expense-panel-meta"></span>
            </header>
            <div class="table-responsive expense-table-wrap">
                <table class="table expense-table expense-transaction-table">
                    <thead><tr><th>Date</th><th>Category &amp; code</th><th>Cost owner</th><th>Workflow</th><th>Notes</th><th>Evidence</th><th class="text-right">Amount</th></tr></thead>
                    <tbody id="expenseTransactionTable"></tbody>
                </table>
            </div>
            <footer class="expense-pagination">
                <button id="expensePreviousPage" type="button" class="btn btn-default btn-sm"><i class="fa fa-angle-left"></i> Previous</button>
                <span id="expensePageLabel">Page 1</span>
                <button id="expenseNextPage" type="button" class="btn btn-default btn-sm">Next <i class="fa fa-angle-right"></i></button>
            </footer>
        </article>

        <div class="expense-methodology">
            <i class="fa fa-info-circle"></i>
            <div><strong>How this report works</strong><p id="expenseMethodology"></p></div>
        </div>
    </div>
</div>

<div class="bi-section-divider"><span>Metric governance catalog</span></div>
