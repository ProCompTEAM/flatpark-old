using DataCenter.Data.Base;

using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class UserSettings : BaseEntity, IUnited
    {
        [Required]
        public string UnitId { get; set; }

        [Required]
        public string Name { get; set; }

        public string Attributes { get; set; }

        public string World { get; set; }

        [Required]
        public double X { get; set; }

        [Required]
        public double Y { get; set; }

        [Required]
        public double Z { get; set; }
    }
}