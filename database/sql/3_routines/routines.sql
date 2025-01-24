-- MariaDB dump 10.19  Distrib 10.11.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: dailytakings
-- ------------------------------------------------------
-- Server version	10.11.6-MariaDB-0+deb12u1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping routines for database 'dailytakings'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `avg_weekly_income` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`%` PROCEDURE `avg_weekly_income`(IN `_SHOPID` INT)
BEGIN
DROP TABLE IF EXISTS weekly_sales;
CREATE TEMPORARY TABLE weekly_sales (
SELECT shopid, YEAR(date) as year, QUARTER(date) as quarter
	,WEEK(date) as week, MONTH(MIN(date)) as quarter_start_month
    ,SUM(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag
FROM takings
WHERE shopid = _SHOPID
GROUP BY YEAR(date)*100+WEEK(date));

SELECT shopid, year, quarter
	, DATE(CONCAT_WS('-', year, quarter_start_month, 1)) as quarter_start
    , (MOD(quarter, 4)+1) as trading_quarter
	, if(quarter=4,year+1,year) as trading_year
	, Round(AVG(sales_plus_rag),2) as avg_weekly_income
    , count(*) AS count
FROM weekly_sales
GROUP BY shopid, year, quarter
ORDER BY year DESC, quarter DESC;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cumm_sales_by_day` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `cumm_sales_by_day`(
	_SHOPID INT,
	_TODAY DATE
)
BEGIN
DECLARE FirstDayOfMonth DATE;
DECLARE MaxDays INT;
DECLARE Target DECIMAL(8,2);
SET FirstDayOfMonth = DATE_FORMAT(_TODAY ,'%Y-%m-01');
SELECT salestarget INTO Target FROM vwshop WHERE id=_SHOPID AND validfrom <= _TODAY AND validuntil > _TODAY;
SET @day=0;
SET @cumm=0;
SET @cummT=0;
DROP TEMPORARY TABLE IF EXISTS TakingsByDayThisMonth;
CREATE TEMPORARY TABLE TakingsByDayThisMonth (
SELECT `date`, @day:=@day+1 as Day,
(clothing+brica+books+linens+donations+other) as sales, rag,
(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
@cumm:=@cumm+(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as Cumm,
Target as target, @cummT:=@cummT+Target as CummTarget
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` >= FirstDayOfMonth AND t.`date` <= _TODAY
ORDER BY t.shopid,`date`
);
SET MaxDays = @day;
SET @day=0;
SET @cumm=0;
SET @cummT=0;
DROP TEMPORARY TABLE IF EXISTS TakingsByDayPreviousMonth;
CREATE TEMPORARY TABLE TakingsByDayPreviousMonth (
SELECT `date`, @day:=@day+1 as Day,
(clothing+brica+books+linens+donations+other) as sales, rag,
(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
@cumm:=@cumm+(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as Cumm,
Target as target, @cummT:=@cummT+Target as CummTarget
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` < FirstDayOfMonth 
	AND t.`date` >= DATE_SUB(FirstDayOfMonth, INTERVAL 1 MONTH)
ORDER BY t.shopid, `date`);
SET @day=0;
SET @cumm=0;
SET @cummT=0;
DROP TEMPORARY TABLE IF EXISTS TakingsByDayPreviousYear;
CREATE TEMPORARY TABLE TakingsByDayPreviousYear (
SELECT `date`, @day:=@day+1 as Day,
(clothing+brica+books+linens+donations+other) as sales, rag,
(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
@cumm:=@cumm+(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as Cumm,
Target as target, @cummT:=@cummT+Target as CummTarget
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` < DATE_SUB(FirstDayOfMonth, INTERVAL 11 MONTH)
	AND t.`date` >= DATE_SUB(FirstDayOfMonth, INTERVAL 12 MONTH)
ORDER BY t.shopid, `date`);
IF @day > MaxDays THEN 
BEGIN
	SET MaxDays = @day; 
    SELECT t1.Day, t2.`date` as Date, t1.`date` as PrevDate, t3.`date` as PYDate,
		t1.target as Target, t1.CummTarget, 
        t1.sales_after_expenses_and_donations as PrevSales,
		t1.Cumm as PrevCumm, t2.sales_after_expenses_and_donations as Sales,
		t2.Cumm as Cumm, t3.sales_after_expenses_and_donations as PYSales, t3.Cumm as PYCumm
    FROM TakingsByDayPreviousMonth t1
    LEFT OUTER JOIN TakingsByDayThisMonth t2 ON t1.Day = t2.Day
    LEFT OUTER JOIN TakingsByDayPreviousYear t3 ON t1.Day = t3.Day
    ORDER BY t1.Day;
END;    
ELSE
    SELECT t1.Day, t1.`date` as Date, t2.`date` as PrevDate, t3.`date` as PYDate,
		t1.target as Target, t1.CummTarget, 
        t2.sales_after_expenses_and_donations as PrevSales,
		t2.Cumm as PrevCumm, t1.sales_after_expenses_and_donations as Sales,
		t1.Cumm as Cumm, t3.sales_after_expenses_and_donations as PYSales, t3.Cumm as PYCumm
    FROM TakingsByDayThisMonth t1
    LEFT OUTER JOIN TakingsByDayPreviousMonth t2 ON t1.Day = t2.Day
    LEFT OUTER JOIN TakingsByDayPreviousYear t3 ON t1.Day = t3.Day
    ORDER BY t1.Day;
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cumm_sales_by_dayofweek` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `cumm_sales_by_dayofweek`(
	_SHOPID INT,
	_TODAY DATE
)
BEGIN
DECLARE FirstDayOfMonth DATE;
DECLARE TradingYearStart DATE;
DECLARE ThisYear INT;
SET FirstDayOfMonth = DATE_FORMAT(_TODAY ,'%Y-%m-01');
SET ThisYear = YEAR(_TODAY);
IF MONTH(_TODAY) >= 10 THEN 
	SET TradingYearStart = DATE_ADD(MAKEDATE(ThisYear,1), INTERVAL 9 MONTH);
ELSE
	SET TradingYearStart = DATE_ADD(MAKEDATE(ThisYear-1,1), INTERVAL 9 MONTH);
END IF;
SELECT 'MTD' as Period, t.shopid
,WEEKDAY(t.date) as WeekDay, DATE_FORMAT(t.date, '%W') as DayOfWeek 
,FirstDayOfMonth as period_start_date, _TODAY as period_end_date
,COUNT(takingsid) as count
,ROUND(AVG(clothing_num),2) as avg_clothing_num
,ROUND(AVG(brica_num),2) as avg_brica_num
,ROUND(AVG(books_num),2) as avg_books_num
,ROUND(AVG(linens_num),2) as avg_linens_num
,ROUND(AVG(donations_num),2) as avg_donations_num
,ROUND(AVG(other_num),2) as avg_other_num
,ROUND(AVG(rag_num),2) as avg_rag_num
,ROUND(AVG(clothing),2) as avg_clothing
,ROUND(AVG(brica),2) as avg_brica
,ROUND(AVG(books),2) as avg_books
,ROUND(AVG(linens),2) as avg_linens
,ROUND(AVG(donations),2) as avg_donations
,ROUND(AVG(other),2) as avg_other
,ROUND(AVG(rag),2) as avg_rag
,ROUND(AVG(clothing+brica+books+linens+donations+other),2) as avg_sales_total
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` >= FirstDayOfMonth AND t.`date` <= _TODAY
GROUP BY WEEKDAY(date), t.shopid
UNION
SELECT 'YTD' as Period, t1.shopid
,WEEKDAY(date) as WeekDay, DATE_FORMAT(date, '%W') as DayOfWeek
,TradingYearStart, _TODAY
,COUNT(takingsid) as count
,ROUND(AVG(clothing_num),2) as avg_clothing_num
,ROUND(AVG(brica_num),2) as avg_brica_num
,ROUND(AVG(books_num),2) as avg_books_num
,ROUND(AVG(linens_num),2) as avg_linens_num
,ROUND(AVG(donations_num),2) as avg_donations_num
,ROUND(AVG(other_num),2) as avg_other_num
,ROUND(AVG(rag_num),2) as avg_rag_num
,ROUND(AVG(clothing),2) as avg_clothing
,ROUND(AVG(brica),2) as avg_brica
,ROUND(AVG(books),2) as avg_books
,ROUND(AVG(linens),2) as avg_linens
,ROUND(AVG(donations),2) as avg_donations
,ROUND(AVG(other),2) as avg_other
,ROUND(AVG(rag),2) as avg_rag
,ROUND(AVG(clothing+brica+books+linens+donations+other),2) as avg_sales_total
FROM takings t1
WHERE t1.shopid = _SHOPID AND t1.`date` >= TradingYearStart AND t1.`date` <= _TODAY
GROUP BY WEEKDAY(t1.date), t1.shopid
ORDER BY Period, shopid, WeekDay;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cumm_sales_by_dept` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `cumm_sales_by_dept`(	
	_SHOPID INT,
	_TODAY DATE
)
BEGIN
DECLARE TradingYearStart DATE;
DECLARE ThisWeek INT;
DECLARE ThisYear INT;
DECLARE Today DATE;
IF (_SHOPID IS NULL OR _SHOPID = 0) THEN
	SET _SHOPID = 1;
END IF;
IF (_TODAY IS NULL OR _TODAY = '') THEN
	SELECT Max(date) INTO Today FROM takings WHERE shopid = _SHOPID;
ELSE
    SET Today = CAST(_TODAY AS DATE);
END IF;
SET ThisWeek = WEEK(Today);
SET ThisYear = YEAR(Today);
IF MONTH(Today) >= 10 THEN 
	SET TradingYearStart = DATE_ADD(MAKEDATE(ThisYear,1), INTERVAL 9 MONTH);
ELSE
	SET TradingYearStart = DATE_ADD(MAKEDATE(ThisYear-1,1), INTERVAL 9 MONTH);
END IF;
SELECT 'TrYTD' as Type
,SUM(clothing_num) as sum_clothing_num
,SUM(brica_num) as sum_brica_num
,SUM(books_num) as sum_books_num
,SUM(linens_num) as sum_linens_num
,SUM(other_num+donations_num) as sum_other_num
,SUM(rag_num) as sum_rag_num
,SUM(clothing) as sum_clothing
,SUM(brica) as sum_brica
,SUM(books) as sum_books
,SUM(linens) as sum_linens
,SUM(donations+other) as sum_other
,SUM(rag) as sum_rag
FROM takings
WHERE shopid = _SHOPID AND `date` >= TradingYearStart AND `date` <= Today
UNION
SELECT 'WTD' as Type
,SUM(clothing_num) as sum_clothing_num
,SUM(brica_num) as sum_brica_num
,SUM(books_num) as sum_books_num
,SUM(linens_num) as sum_linens_num
,SUM(other_num+donations_num) as sum_other_num
,SUM(rag_num) as sum_rag_num
,SUM(clothing) as sum_clothing
,SUM(brica) as sum_brica
,SUM(books) as sum_books
,SUM(linens) as sum_linens
,SUM(donations+other) as sum_other
,SUM(rag) as sum_rag
FROM takings
WHERE shopid = _SHOPID AND YEAR(`date`) = ThisYear AND Week(`date`) = ThisWeek
UNION
SELECT 'MTD' as Type
,SUM(clothing_num) as sum_clothing_num
,SUM(brica_num) as sum_brica_num
,SUM(books_num) as sum_books_num
,SUM(linens_num) as sum_linens_num
,SUM(other_num+donations_num) as sum_other_num
,SUM(rag_num) as sum_rag_num
,SUM(clothing) as sum_clothing
,SUM(brica) as sum_brica
,SUM(books) as sum_books
,SUM(linens) as sum_linens
,SUM(donations+other) as sum_other
,SUM(rag) as sum_rag
FROM takings
WHERE shopid = _SHOPID AND `date` >= DATE_FORMAT(Today ,'%Y-%m-01') AND `date` <= Today
UNION
SELECT 'YTD' as Type
,SUM(clothing_num) as sum_clothing_num
,SUM(brica_num) as sum_brica_num
,SUM(books_num) as sum_books_num
,SUM(linens_num) as sum_linens_num
,SUM(other_num+donations_num) as sum_other_num
,SUM(rag_num) as sum_rag_num
,SUM(clothing) as sum_clothing
,SUM(brica) as sum_brica
,SUM(books) as sum_books
,SUM(linens) as sum_linens
,SUM(donations+other) as sum_other
,SUM(rag) as sum_rag
FROM takings
WHERE shopid = _SHOPID AND YEAR(`date`) = ThisYear AND `date` <= Today;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cumm_sales_by_month` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `cumm_sales_by_month`(
	_SHOPID INT,
	_STARTDATE VARCHAR(20),
    _TARGET DECIMAL (8,2)
)
BEGIN
DECLARE start_date DATE;
SET start_date = CAST(_STARTDATE AS DATE);
DROP TEMPORARY TABLE IF EXISTS TakingsByWeekNumber;
CREATE TEMPORARY TABLE TakingsByWeekNumber (
SELECT 'CP' as Period, t.shopid, `date`, Month(`date`) as MonthNo
, COUNT(takingsid) as count
,SUM(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) 
	as sum_total_after_expenses_and_donations
,_TARGET*COUNT(takingsid) as target
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` >= start_date
GROUP BY Month(`date`)
ORDER BY `date`
);
INSERT INTO TakingsByWeekNumber
SELECT 'PY' as Period, t.shopid, `date`, Month(`date`) 
, COUNT(takingsid) as count
,SUM(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) 
	as sum_total_after_expenses_and_donations
,_TARGET*COUNT(takingsid) as target
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` < start_date AND t.`date` >= DATE_SUB(start_date, INTERVAL 1 YEAR)
GROUP BY Month(`date`)
ORDER BY `date`;
SET @cumm=0;
SET @week=0;
SET @cummT=0;
DROP TEMPORARY TABLE IF EXISTS Cummulative_Takings;
CREATE TEMPORARY TABLE Cummulative_Takings (
SELECT *
,@cumm:=@cumm+sum_total_after_expenses_and_donations as cummumlative_sales
,@week:=@week+1 as NewMonthNo
,@cummT:=@cummT+target as cummulative_target
FROM TakingsByWeekNumber
WHERE Period = 'CP');
SET @cumm=0;
SET @week=0;
SET @cummT=0;
DROP TEMPORARY TABLE IF EXISTS Cummulative_Takings2;
CREATE TEMPORARY TABLE Cummulative_Takings2 (
SELECT *, @cumm:=@cumm+sum_total_after_expenses_and_donations as cummumlative_sales
,@week:=@week+1 as NewMonthNo
,@cummT:=@cummT+target as cummulative_target
FROM TakingsByWeekNumber
WHERE Period = 'PY'
ORDER BY Period, `date`);
SELECT 
    ct2.NewMonthNo, ct2.shopid, DATE_ADD(ct2.`date`, INTERVAL 1 YEAR) as `date`
    , ct2.sum_total_after_expenses_and_donations as Prev_Monthly_Sales
    , IFNULL(ct1.sum_total_after_expenses_and_donations, 0.00) as Monthly_Sales
    , ct2.cummumlative_sales as Prev_Cumm_Sales
    , IFNULL(ct1.cummumlative_sales,0.00) as Cumm_Sales
    , ct2.cummulative_target
FROM
    Cummulative_Takings2 ct2
    LEFT OUTER JOIN Cummulative_Takings ct1 
		ON ct2.NewMonthNo = ct1.NewMonthNo AND ct2.shopid = ct1.shopid
WHERE ct2.Period = 'PY'
ORDER BY ct2.Period , ct2.`date`;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cumm_sales_by_week` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `cumm_sales_by_week`(
	_SHOPID INT,
	_STARTDATE VARCHAR(20),
    _TARGET DECIMAL (8,2)
)
BEGIN
DECLARE start_date DATE;
SET start_date = CAST(_STARTDATE AS DATE);
DROP TEMPORARY TABLE IF EXISTS TakingsByWeekNumber;
CREATE TEMPORARY TABLE TakingsByWeekNumber (
SELECT 'CP' as Period, t.shopid, `date`, Week(`date`) as WeekNo
, COUNT(takingsid) as count
,SUM(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) 
	as sum_total_after_expenses_and_donations
,_TARGET*COUNT(takingsid) as target
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` >= start_date
GROUP BY t.shopid, Week(`date`)
ORDER BY t.shopid,`date`
);
INSERT INTO TakingsByWeekNumber
SELECT 'PY' as Period, t.shopid, `date`, Week(`date`) as WeekNo
, COUNT(takingsid) as count
,SUM(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) 
	as sum_total_after_expenses_and_donations
,_TARGET*COUNT(takingsid) as target
FROM takings t
WHERE t.shopid = _SHOPID AND t.`date` < start_date AND t.`date` >= DATE_SUB(start_date, INTERVAL 1 YEAR)
GROUP BY t.shopid, Week(`date`)
ORDER BY t.shopid, `date`;
SET @cumm=0;
SET @week=0;
SET @cummT=0;
DROP TEMPORARY TABLE IF EXISTS Cummulative_Takings;
CREATE TEMPORARY TABLE Cummulative_Takings (
SELECT *
,@cumm:=@cumm+sum_total_after_expenses_and_donations as cummumlative_sales
,@week:=@week+1 as NewWeekNo
,@cummT:=@cummT+target as cummulative_target
FROM TakingsByWeekNumber
WHERE Period = 'CP');
SET @cumm=0;
SET @week=0;
SET @cummT=0;
DROP TEMPORARY TABLE IF EXISTS Cummulative_Takings2;
CREATE TEMPORARY TABLE Cummulative_Takings2 (
SELECT *, @cumm:=@cumm+sum_total_after_expenses_and_donations as cummumlative_sales
,@week:=@week+1 as NewWeekNo
,@cummT:=@cummT+target as cummulative_target
FROM TakingsByWeekNumber
WHERE Period = 'PY'
ORDER BY Period, `date`);
SELECT 
    ct2.NewWeekNo, ct2.shopid, DATE_ADD(ct2.`date`, INTERVAL 1 YEAR) as `date`
    , ct2.sum_total_after_expenses_and_donations as Prev_Weekly_Sales
    , IFNULL(ct1.sum_total_after_expenses_and_donations, 0.00) as Weekly_Sales
    , ct2.cummumlative_sales as Prev_Cumm_Sales
    , IFNULL(ct1.cummumlative_sales,0.00) as Cumm_Sales
    , ct2.cummulative_target
FROM
    Cummulative_Takings2 ct2
    LEFT OUTER JOIN Cummulative_Takings ct1 ON ct2.NewWeekNo = ct1.NewWeekNo AND ct2.shopid = ct1.shopid
WHERE ct2.Period = 'PY'
ORDER BY ct2.Period , ct2.`date`;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `rag_sales_over_time` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `rag_sales_over_time`(
	_SHOPID INT,
	_ENDDATE VARCHAR(20),
    _TERM VARCHAR(1)
    )
BEGIN
DECLARE start_date DATE;
DECLARE end_date DATE;
IF (_ENDDATE IS NULL OR _ENDDATE = '') THEN
	SET end_date = CURDATE();
ELSE
    SET end_date = CAST(_ENDDATE AS DATE);
END IF;
CASE
	WHEN (UPPER(_TERM)='Q') THEN
		BEGIN
			SET end_date = DATE_ADD(STR_TO_DATE(CONCAT(YEAR(end_date),'-'
				,LPAD(1+3*(CEILING(MONTH(end_date)/3)-1),2,'00'),'-01'), '%Y-%m-%d'), INTERVAL 3 MONTH);
			SET start_date = DATE_SUB(end_date, INTERVAL 24 MONTH);     
			SELECT STR_TO_DATE(CONCAT(YEAR(date),'-',LPAD(MONTH(date),2,'00'),'-01'), '%Y-%m-%d') as 'date'
				,CONCAT(YEAR(date),'-Q',CEILING(MONTH(date)/3)) as label
				,SUM(rag) as rag
			FROM takings
			WHERE shopid = _SHOPID AND takings.date >= start_date AND takings.date < end_date
			GROUP BY CONCAT(YEAR(date),'-Q',CEILING(MONTH(date)/3))
			ORDER BY takings.date;
		END;
	WHEN (UPPER(_TERM)='M') THEN
		BEGIN
			SET end_date = DATE_ADD(
				STR_TO_DATE(CONCAT(YEAR(end_date),'-',LPAD(MONTH(end_date),2,'00'),'-01'), '%Y-%m-%d')
					, INTERVAL 1 MONTH);
			SET start_date = DATE_SUB(end_date, INTERVAL 24 MONTH);        
			SELECT STR_TO_DATE(CONCAT(YEAR(date),'-',LPAD(MONTH(date),2,'00'),'-01'), '%Y-%m-%d') as 'date'
				,DATE_FORMAT(date, "%b-%y")  as label
				,SUM(rag) as rag
			FROM takings
			WHERE shopid = _SHOPID AND takings.date >= start_date AND takings.date < end_date
			GROUP BY YEAR(date),MONTH(date)
			ORDER BY takings.date;
		END;
	WHEN (UPPER(_TERM)='Y') THEN
		BEGIN
			SET end_date = STR_TO_DATE(CONCAT(YEAR(end_date)+1,'-01-01'), '%Y-%m-%d');                    
			SET start_date = STR_TO_DATE('2011-01-01', '%Y-%m-%d');       
			SELECT STR_TO_DATE(CONCAT(YEAR(date),'-01-01'), '%Y-%m-%d') as 'date'
				,DATE_FORMAT(date, "%Y")  as label
				,SUM(rag) as rag
			FROM takings
			WHERE shopid = _SHOPID AND takings.date >= start_date AND takings.date < end_date
			GROUP BY YEAR(date)
			ORDER BY takings.date;
		END;
	WHEN (UPPER(_TERM)='W') THEN
		BEGIN                 
			SET start_date = DATE_SUB(end_date, INTERVAL 20 WEEK);     
			SELECT MIN(date) as 'date'
				,DATE_FORMAT(date, "Week-%u")  as label
				,SUM(rag) as rag
			FROM takings
			WHERE shopid = _SHOPID AND takings.date >= start_date AND takings.date < end_date
			GROUP BY WEEK(date)
			ORDER BY takings.date;
		END;
END CASE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sales_chart` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `sales_chart`(IN `_SHOPID` INT, IN `_TODAY` DATE, IN `_NUMBER` INT UNSIGNED)
BEGIN
DECLARE Today DATE;
DECLARE FirstDay DATE;
DECLARE AvgSales DECIMAL(8,2);
DECLARE AvgSalesLast30Days DECIMAL(8,2);
DECLARE AvgSalesLast365Days DECIMAL(8,2);
DECLARE AvgSalesEver DECIMAL(8,2);
IF (_SHOPID IS NULL OR _SHOPID = 0) THEN
	SET _SHOPID = 1;
END IF;
IF (_TODAY IS NULL OR _TODAY = '') THEN
	SET Today = CURDATE();
ELSE
    SET Today = CAST(_TODAY AS DATE);
END IF;
IF (_NUMBER IS NULL OR _NUMBER = 0) THEN
	SET _NUMBER = 10;
END IF;
DROP TEMPORARY TABLE IF EXISTS sales_chart;


CREATE TEMPORARY TABLE sales_chart (
SELECT takingsid, date, UNIX_TIMESTAMP(`date`)*1000 +61200000 as sales_date, (clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference) as net_sales
FROM takings
WHERE shopid = _SHOPID AND date <= Today
ORDER BY date DESC
LIMIT _NUMBER);
SELECT MIN(date) INTO FirstDay FROM sales_chart;
SELECT AVG(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference) INTO AvgSales
FROM takings
WHERE shopid = _SHOPID AND date <= Today AND date >= FirstDay
ORDER BY date DESC
LIMIT 10;
SELECT AVG(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)
INTO AvgSalesLast30Days
FROM takings
WHERE shopid = _SHOPID AND `date` <= Today AND `date` >= DATE_SUB(Today, INTERVAL 1 MONTH);
SELECT AVG(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference)
INTO AvgSalesLast365Days
FROM takings
WHERE shopid = _SHOPID AND `date` <= Today AND `date` >= DATE_SUB(Today, INTERVAL 12 MONTH);
SELECT AVG(clothing+brica+books+linens+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference) 
INTO AvgSalesEver
FROM takings
WHERE shopid = _SHOPID AND `date` <= Today;
SELECT 
    s1.*,
    AvgSales as AvgSales,
    AvgSalesLast30Days as AvgSalesLast30Days,
    AvgSalesLast365Days as AvgSalesLast365Days,
    AvgSalesEver as AvgSalesEver
FROM
    sales_chart s1
ORDER BY s1.date;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sales_table` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`shop`@`localhost` PROCEDURE `sales_table`(IN `_SHOPID` INT, IN `_TODAY` DATE)
BEGIN
DECLARE Today DATE;
DECLARE Yesterday DATE;
DECLARE ThisYear INT;
DECLARE ThisWeekNo INT;
DECLARE LastWeekNo INT;
DECLARE MonthAgo DATE;
DECLARE LastMonthNo INT;
DECLARE LastMonthYear INT;
DECLARE PrevMonthNo INT;
DECLARE PrevMonthYear INT;
DECLARE TradingYearStart DATE;
IF (_TODAY IS NULL OR _TODAY = '') THEN
	SET Today = CURDATE();
ELSE
    SET Today = CAST(_TODAY AS DATE);
END IF;
IF (_SHOPID IS NULL OR _SHOPID = '') THEN
	SET _SHOPID = 1;
END IF;
SELECT `date` INTO Yesterday FROM takings 
WHERE shopid = _SHOPID  AND `date` <> Today ORDER BY date DESC 
LIMIT 0,1;
SET ThisYear = YEAR(Today);
SET ThisWeekNo = Week(Today);
SET LastWeekNo = ThisWeekNo-1;
IF LastWeekNo < 0  THEN SET LastWeekNo = 0; END IF;
SET MonthAgo = DATE_SUB(Today, INTERVAL 1 MONTH);
SET LastMonthNo = MONTH(MonthAgo);
SET LastMonthYear = YEAR(MonthAgo);
SET MonthAgo = DATE_SUB(MonthAgo, INTERVAL 1 MONTH);
SET PrevMonthNo = MONTH(MonthAgo);
SET PrevMonthYear = YEAR(MonthAgo);
IF MONTH(Today) >= 10 THEN 
	SET TradingYearStart = DATE_ADD(MAKEDATE(ThisYear,1), INTERVAL 9 MONTH);
ELSE
	SET TradingYearStart = DATE_ADD(MAKEDATE(ThisYear-1,1), INTERVAL 9 MONTH);
END IF;
DROP TEMPORARY TABLE IF EXISTS sales_summary;
CREATE TEMPORARY TABLE sales_summary (
SELECT 1 AS `index`, 'Yesterday' as Period, 'Actual  ' as `Type`,
date as start_date, date as end_date, 
COUNT(takingsid) as count,
clothing_num,brica_num,books_num,linens_num,donations_num,other_num,rag_num,
(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as number_of_items_sold,
clothing,brica,books,linens,donations,other,rag,
customers_num_total,
cash_to_bank,
credit_cards,
( operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
SUM(clothing+brica+books+linens+donations+other) as sales,
SUM(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
SUM(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND `date` = Yesterday);
INSERT INTO sales_summary
SELECT 0 AS `Index`, 'Today' as Period, 'Actual' as Type,
IFNull(date, Today) as StartDate, IFNull(date, Today) as EndDate, 
IFNULL(COUNT(takingsid),0) as count,
clothing_num,brica_num,books_num,linens_num,donations_num,other_num,rag_num,
(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as number_of_items_sold,
clothing,brica,books,linens,donations,other,rag,
customers_num_total,
cash_to_bank,
credit_cards,
( operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
SUM(clothing+brica+books+linens+donations+other) as sales,
SUM(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
SUM(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND `date` = Today;
INSERT INTO sales_summary
SELECT 2 AS `Index`, 'This Week' as Period, 'Average' as `Type`,
Min(date) as StartDate, Max(date) as EndDate, COUNT(takingsid) as count
,AVG(clothing_num) as avg_clothing_num
,AVG(brica_num) as avg_brica_num
,AVG(books_num) as avg_books_num
,AVG(linens_num) as avg_linens_num
,AVG(donations_num) as avg_donations_num
,AVG(other_num) as avg_other_num
,AVG(rag_num) as avg_rag_num
,AVG(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as avg_transactions_num_total
,AVG(clothing) as avg_clothing
,AVG(brica) as avg_brica
,AVG(books) as avg_books
,AVG(linens) as avg_linens
,AVG(donations) as avg_donations
,AVG(other) as avg_other
,AVG(rag) as avg_rag
,AVG(customers_num_total) as avg_customers_num_total
,AVG(cash_to_bank) as avg_cash_to_bank
,AVG(credit_cards) as avg_credit_cards,
AVG(operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
AVG(clothing+brica+books+linens+donations+other) as sales,
AVG(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
AVG(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND Week(`date`) = ThisWeekNo AND Year(`date`) = ThisYear;
INSERT INTO sales_summary
SELECT 3 AS `Index`, 'Last Week' as Period, 'Average' as `Type`,
Min(date) as StartDate, Max(date) as EndDate, COUNT(takingsid) as count
,AVG(clothing_num) as avg_clothing_num
,AVG(brica_num) as avg_brica_num
,AVG(books_num) as avg_books_num
,AVG(linens_num) as avg_linens_num
,AVG(donations_num) as avg_donations_num
,AVG(other_num) as avg_other_num
,AVG(rag_num) as avg_rag_num
,AVG(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as avg_transactions_num_total
,AVG(clothing) as avg_clothing
,AVG(brica) as avg_brica
,AVG(books) as avg_books
,AVG(linens) as avg_linens
,AVG(donations) as avg_donations
,AVG(other) as avg_other
,AVG(rag) as avg_rag
,AVG(customers_num_total) as avg_customers_num_total
,AVG(cash_to_bank) as avg_cash_to_bank
,AVG(credit_cards) as avg_credit_cards,
AVG(operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
AVG(clothing+brica+books+linens+donations+other) as sales,
AVG(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
AVG(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND Week(`date`) = LastWeekNo AND Year(`date`) = ThisYear;
INSERT INTO sales_summary
SELECT 4 AS `Index`, 'MTD' as Period, 'Average' as `Type`
,DATE_ADD(MAKEDATE(ThisYear,1), INTERVAL Month(Today)-1 MONTH) as StartDate
,Max(date) as EndDate, COUNT(takingsid) as count
,AVG(clothing_num) as avg_clothing_num
,AVG(brica_num) as avg_brica_num
,AVG(books_num) as avg_books_num
,AVG(linens_num) as avg_linens_num
,AVG(donations_num) as avg_donations_num
,AVG(other_num) as avg_other_num
,AVG(rag_num) as avg_rag_num
,AVG(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as avg_transactions_num_total
,AVG(clothing) as avg_clothing
,AVG(brica) as avg_brica
,AVG(books) as avg_books
,AVG(linens) as avg_linens
,AVG(donations) as avg_donations
,AVG(other) as avg_other
,AVG(rag) as avg_rag
,AVG(customers_num_total) as avg_customers_num_total
,AVG(cash_to_bank) as avg_cash_to_bank
,AVG(credit_cards) as avg_credit_cards,
AVG(operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
AVG(clothing+brica+books+linens+donations+other) as sales,
AVG(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
AVG(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND Month(`date`) = Month(Today) AND Year(`date`) = ThisYear;
INSERT INTO sales_summary
SELECT 5 AS `Index`, 'LastMonth' as Period, 'Average' as `Type`
,Min(date) as StartDate, Max(date) as EndDate, COUNT(takingsid) as count
,AVG(clothing_num) as avg_clothing_num
,AVG(brica_num) as avg_brica_num
,AVG(books_num) as avg_books_num
,AVG(linens_num) as avg_linens_num
,AVG(donations_num) as avg_donations_num
,AVG(other_num) as avg_other_num
,AVG(rag_num) as avg_rag_num
,AVG(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as avg_transactions_num_total
,AVG(clothing) as avg_clothing
,AVG(brica) as avg_brica
,AVG(books) as avg_books
,AVG(linens) as avg_linens
,AVG(donations) as avg_donations
,AVG(other) as avg_other
,AVG(rag) as avg_rag
,AVG(customers_num_total) as avg_customers_num_total
,AVG(cash_to_bank) as avg_cash_to_bank
,AVG(credit_cards) as avg_credit_cards,
AVG(operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
AVG(clothing+brica+books+linens+donations+other) as sales,
AVG(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
AVG(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND Month(`date`) = LastMonthNo AND Year(`date`) = LastMonthYear;
INSERT INTO sales_summary
SELECT 6 AS `Index`, 'PrevMonth' as Period, 'Average' as `Type`
,Min(date) as StartDate, Max(date) as EndDate, COUNT(takingsid) as count
,AVG(clothing_num) as avg_clothing_num
,AVG(brica_num) as avg_brica_num
,AVG(books_num) as avg_books_num
,AVG(linens_num) as avg_linens_num
,AVG(donations_num) as avg_donations_num
,AVG(other_num) as avg_other_num
,AVG(rag_num) as avg_rag_num
,AVG(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as avg_transactions_num_total
,AVG(clothing) as avg_clothing
,AVG(brica) as avg_brica
,AVG(books) as avg_books
,AVG(linens) as avg_linens
,AVG(donations) as avg_donations
,AVG(other) as avg_other
,AVG(rag) as avg_rag
,AVG(customers_num_total) as avg_customers_num_total
,AVG(cash_to_bank) as avg_cash_to_bank
,AVG(credit_cards) as avg_credit_cards,
AVG(operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
AVG(clothing+brica+books+linens+donations+other) as sales,
AVG(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
AVG(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND Month(`date`) = PrevMonthNo AND Year(`date`) = PrevMonthYear;
INSERT INTO sales_summary
SELECT 7 AS `Index`, 'YTD' as Period, 'Average' as `Type`
,Min(date) as StartDate, Max(date) as EndDate, COUNT(takingsid) as count
,AVG(clothing_num) as avg_clothing_num
,AVG(brica_num) as avg_brica_num
,AVG(books_num) as avg_books_num
,AVG(linens_num) as avg_linens_num
,AVG(donations_num) as avg_donations_num
,AVG(other_num) as avg_other_num
,AVG(rag_num) as avg_rag_num
,AVG(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as avg_transactions_num_total
,AVG(clothing) as avg_clothing
,AVG(brica) as avg_brica
,AVG(books) as avg_books
,AVG(linens) as avg_linens
,AVG(donations) as avg_donations
,AVG(other) as avg_other
,AVG(rag) as avg_rag
,AVG(customers_num_total) as avg_customers_num_total
,AVG(cash_to_bank) as avg_cash_to_bank
,AVG(credit_cards) as avg_credit_cards,
AVG(operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
AVG(clothing+brica+books+linens+donations+other) as sales,
AVG(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
AVG(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND `date` >= TradingYearStart;
INSERT INTO sales_summary
SELECT 8 AS `Index`, 'Last Year' as Period, 'Average' as `Type`
,Min(date) as StartDate, Max(date) as EndDate, COUNT(takingsid) as count
,AVG(clothing_num) as avg_clothing_num
,AVG(brica_num) as avg_brica_num
,AVG(books_num) as avg_books_num
,AVG(linens_num) as avg_linens_num
,AVG(donations_num) as avg_donations_num
,AVG(other_num) as avg_other_num
,AVG(rag_num) as avg_rag_num
,AVG(clothing_num+brica_num+books_num+linens_num+donations_num+other_num+rag_num) as avg_transactions_num_total
,AVG(clothing) as avg_clothing
,AVG(brica) as avg_brica
,AVG(books) as avg_books
,AVG(linens) as avg_linens
,AVG(donations) as avg_donations
,AVG(other) as avg_other
,AVG(rag) as avg_rag
,AVG(customers_num_total) as avg_customers_num_total
,AVG(cash_to_bank) as avg_cash_to_bank
,AVG(credit_cards) as avg_credit_cards,
AVG(operating_expenses + volunteer_expenses + other_adjustments - cash_difference) as expenses,
AVG(clothing+brica+books+linens+donations+other) as sales,
AVG(clothing+brica+books+linens+donations+other+rag) as sales_plus_rag,
AVG(clothing+brica+books+linens+donations+other+rag-operating_expenses-volunteer_expenses-other_adjustments+cash_difference-donations) as sales_after_expenses_and_donations,
SUM(clothing+brica+books+linens+other+rag+cash_difference) as total_sales
FROM takings
WHERE shopid = _SHOPID AND `date` < TradingYearStart AND `date` >= DATE_SUB(TradingYearStart, INTERVAL 1 YEAR);
SELECT *, _SHOPID as shopid FROM sales_summary ORDER BY `index`;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-24  3:02:54
