-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the Contao    *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_user`
--

CREATE TABLE `tl_user` (
 `iso_reports` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
 `iso_reports` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_orderstatus`
--

CREATE TABLE `tl_iso_orderstatus` (
 `showInReports` char(1) NOT NULL default '1',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
