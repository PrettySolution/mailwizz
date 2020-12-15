-- -----------------------------------------------------
-- Table `tour_slideshow`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tour_slideshow` (
  `slideshow_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `application` VARCHAR(45) NOT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`slideshow_id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

 
-- -----------------------------------------------------
-- Table `tour_slideshow_slides`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tour_slideshow_slide` (
  `slide_id` INT NOT NULL AUTO_INCREMENT,
  `slideshow_id` INT NOT NULL,
  `title` VARCHAR(100) NULL,
  `content` TEXT NOT NULL,
  `image` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`slide_id`),
  INDEX `fk_tour_slideshow_slide_tour_slideshow1_idx` (`slideshow_id` ASC),
  CONSTRAINT `fk_tour_slideshow_slide_tour_slideshow1`
    FOREIGN KEY (`slideshow_id`)
    REFERENCES `tour_slideshow` (`slideshow_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;