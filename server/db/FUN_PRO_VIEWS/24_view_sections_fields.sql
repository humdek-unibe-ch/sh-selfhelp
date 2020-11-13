DROP VIEW IF EXISTS view_sections_fields;
CREATE VIEW view_sections_fields
AS
SELECT
   s.id AS id_sections,
   s.name AS section_name,
   sft.content,
   s.id_styles,
   fields.style_name,
   field_id AS id_fields,
   field_name,
   l.locale,
   g.name AS gender 
FROM sections s 
INNER JOIN sections_fields_translation sft ON (sft.id_sections = s.id) 
INNER JOIN view_style_fields fields ON (fields.style_id = s.id_styles) 
INNER JOIN languages l ON (sft.id_languages = l.id) 
INNER JOIN genders g ON (sft.id_genders = g.id);