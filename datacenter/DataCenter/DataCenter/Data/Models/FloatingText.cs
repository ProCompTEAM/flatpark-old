using DataCenter.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class FloatingText : BaseEntity, IUnited, ICreatedDate
    {
        [Required]
        public string Text { get; set; }

        [Required]
        public string UnitId { get; set; }

        [Required]
        public string World { get; set; }

        [Required]
        public double X { get; set; }

        [Required]
        public double Y { get; set; }

        [Required]
        public double Z { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}