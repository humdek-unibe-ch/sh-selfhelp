DROP VIEW IF EXISTS view_sections_fields;
CREATE VIEW view_sections_fields
AS
SELECT
   s.id AS id_sections,
   s.name AS section_name,
   IFNULL(sft.content, '') AS content,
   IFNULL(sft.meta, '') AS meta,
   s.id_styles,
   fields.style_name,
   field_id AS id_fields,
   field_name,
   IFNULL(l.locale, '') AS locale
FROM sections s 
LEFT JOIN view_style_fields fields ON (fields.style_id = s.id_styles) 
LEFT JOIN sections_fields_translation sft ON (sft.id_sections = s.id AND sft.id_fields = fields.field_id) 
LEFT JOIN languages l ON (sft.id_languages = l.id);
