using DataCenter.Data.Base;
using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class Credentials : BaseEntity
    {
        [Required, MaxLength(36)]
        public string GeneratedToken { get; set; }

        public string Tag { get; set; }
    }
}