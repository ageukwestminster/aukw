<?php

namespace Models;

/**
 * This is a subset of the alloweed predefined date ranges that can be used with 
 * QuickBooks reports. The full list is:
 * Today, Yesterday, This Week, Last Week, This Week-to-date, Last Week-to-date, 
 * Next Week, Next 4 Weeks, This Month, Last Month, This Month-to-date, 
 * Last Month-to-date, Next Month, This Fiscal Quarter, Last Fiscal Quarter, 
 * This Fiscal Quarter-to-date, Last Fiscal Quarter-to-date, Next Fiscal Quarter, 
 * This Fiscal Year, Last Fiscal Year, This Fiscal Year-to-date, 
 * Last Fiscal Year-to-date, Next Fiscal Year
 * 
 * @category Enum
 */
enum QbDateMacro: string
{
    case TODAY = 'today'; // Both 'Today' and 'today' seem to be accepted
    case YESTERDAY = 'yesterday'; // Both 'Yesterday' and 'yesterday' seem to be accepted
    case THIS_WEEK = 'thisweek';
    case THIS_MONTH = 'thismonth';
    case THIS_QUARTER = 'thisfiscalquarter';
    case THIS_TRADING_YEAR = 'thisfiscalyear';
    case THIS_YEAR = 'thisyear';
    case LAST_WEEK = 'lastweek';
    case LAST_MONTH = 'lastmonth';
    case LAST_QUARTER = 'lastfiscalquarter';
    case LAST_TRADING_YEAR = 'lastfiscalyear';
    case LAST_YEAR = 'lastyear';
}
