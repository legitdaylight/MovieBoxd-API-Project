ALTER TABLE Movies DROP INDEX title;

ALTER TABLE Movies ADD UNIQUE(title, release_date);